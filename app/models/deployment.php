<?php
class Deployment extends AppModel {

	var $name = 'Deployment';

	var $Project = null;
	
	var $DeploymentLog = null;
	
	var $useTable = false;
	
	var $process = array(
//		0 => 'initialize',
		1 => 'export',
		2 => 'synchronize',
		3 => 'finalize'
	);
	
	var $uuid = '';
	
	var $lastExecutionTime = 0;
	
	var $lastError = '';
	
	function __construct() {
		parent::__construct();
		
		App::import('Model','Project');
		$this->Project = new Project(); 
		
		App::import('Model','DeploymentLog');
		$this->DeploymentLog = new DeploymentLog();

		if ( Configure::read() > 0 )
			if (!class_exists('CakeLog'))
				uses('cake_log');
	}// __construct 
	
	// Public deploy -----------------------------------------------------------------		
	/**
	 * Run a complete deployment process
	 * @param int $project_id 		Id of the project that should be deployed 
	 * @param array $options		Various options used for configuring the step 
	 * @return string 			Shell output 
     */	
	function runProcess($project_id = null, $uuid = null, $options = array()) {
		if ( $project_id == null || !($project = $this->Project->read(null, $project_id)) || $uuid == null ) { 
			$this->lastExecutionTime = 0;
			return false;		
		}
				
		$t1 = getMicrotime();
		
		// Init options
		$default_options = array(
			'backup'			=>	false,
			'simulation' 		=> 	false,
			'renamePrdFile' 	=> 	false,
			'changeFileMode' 	=> 	false,
			'giveWriteMode'		=> 	false
		);
		
		$options = array_merge($default_options, $options);
	
		// Prepare log output
		$header = date('Y-m-d H:i:s') . " Performing fast deploy\n";
		$filename = _DEPLOYLOGDIR . DS . $uuid . '.log';
		clearstatcache(); // Prevent pb with is_dir() function (see PHPDoc)
		if (!is_dir(_DEPLOYLOGDIR)) {
			if (!@mkdir(_DEPLOYLOGDIR, octdec(_DIRMODE), TRUE)) {
				$this->triggerError( "Unable to create directory "._DEPLOYLOGDIR );
				return false;
			}
		}
		$log = new File($filename, true);
		if ($log->writable()) {
			$log->append($header);
		}
		
		// Performs successively each step
		//$output = "> Initialize:\n";
		//$output .= $this->initialize($project, $options);
		
		$output = '';
		
		$output .= "\n> Performing step Export:\n";
		if ($shell = $this->runStep('export', $project_id, $uuid, $options)) {
			$output .= $shell;
			preg_match('/ ([0-9]+)\.$/', $output, $matches);
			if (isset($matches[1])) {
				$options['comment'] = __('Revision exported ', true) . $matches[1];			
			}
		} else {
			if ($log->writable()) {
				$log->append("Process aborted (see error.log for further details)");
			}
			return false;
		}	

		$output .= "\n> Performing step Synchronize:\n";
		if ($shell = $this->runStep('synchronize', $project_id, $uuid, $options)) {
			$output .= $shell;
		} else {
			if ($log->writable()) {
				$log->append("Process aborted (see error.log for further details)");
			}
			return false;
		}
		
		$output .= "\n> Performing step Finalize:\n";
		if ($shell = $this->runStep('finalize', $project_id, $uuid, $options)) {
			$output .= $shell;
		} else {
			if ($log->writable()) {
				$log->append("Process aborted (see error.log for further details)");
			}
			return false;
		}
		
		$this->lastExecutionTime = round((getMicrotime() - $t1) , 3);
		
		// Log text
		if ($log->writable()) {
			$log->append("Process executed in ".$this->lastExecutionTime."s");
		}
		
		return $output;
	}// runProcess
	
	/**
	 * Run a deployment step and performs the required checks
	 * @param string $step			Step that should be performed 
	 * @param int $project_id 		Id of the project that should be deployed 
	 * @param array $options		Various options used for configuring the step 
	 * @return string 				Shell output 
     */			
	function runStep($step = null, $project_id = null, $uuid = null, $options = array()) {
		if ( ($step == null && !in_array($step, $this->$process)) || $uuid == null ) {
			return false;
		}
		
		$this->uuid = $uuid;
			
		$t1 = getMicrotime();

		// Prepare log output
		$header = date('Y-m-d H:i:s') . " Output of " . strtoupper($step)."\n";
		$filename = _DEPLOYLOGDIR . DS . $uuid . '.log';
		clearstatcache(); // Prevent pb with is_dir() function (see PHPDoc)
		if (!is_dir(_DEPLOYLOGDIR)) {
			if (!@mkdir(_DEPLOYLOGDIR, octdec(_DIRMODE), TRUE)) {
				$this->triggerError( "Unable to create directory "._DEPLOYLOGDIR );
				return false;
			}
		}
		$log = new File($filename, true);
		if ($log->writable()) {
			$log->append($header);
		}
		// Execute step
 		if ( $project_id == null || !($project = $this->Project->read(null, $project_id)) )  {
			$text = $this->lastError = 'Invalid input parameters';
			$output = false;
		} else {
			$text = $output = $this->{$step}($project, $options);
		} 
		
		$this->lastExecutionTime = round((getMicrotime() - $t1) , 3);
		
		// Log text
		if ($log->writable()) {
			$log->append("<<<<\n".$text."\n>>>> Step executed in ".$this->lastExecutionTime."s\n");
		}
		
		return $output;
	}// runStep
		
	//  Private Step -----------------------------------------------------------
//	/**
//	 * Step 1 of the deployment process: Initialize
//	 * @param array $project 	Project that should be deployed 
//	 * @param array $options	Various options used for configuring the step 
//	 * @return string 			Shell output 
//   */
//	private function initialize($project = null, $options = array()) {
//		if ($project===null) 
//			return false;
//		
//		set_time_limit(_TIMELIMIT_INITIALIZE);
//
//		return '';
//	}// initialize
	
	/**
	 * Step 2 of the deployment process: Export
	 * @param array $project 	Project that should be deployed 
	 * @param array $options	Various options used for configuring the step 
	 * @return string 			Shell output 
     */
	
	private function export($project = null, $options = array()) {
		if ($project===null) {
			return false;
		}
		
		// Set a time limit	
		set_time_limit(_TIMELIMIT_EXPORT);
				
		// Define step options
		$default_options = array(
			'revision' 		=> 	null,
			'user_svn' 		=> 	Configure::read('Subversion.user'),
			'password_svn' 	=> 	Configure::read('Subversion.passwd')
		);
		$options = array_merge($default_options, $options);
		$output = '';
		
		// Create temporary folders for the current project (if required)
		$exportDir = _DEPLOYTMPDIR . DS . $project['Project']['name'];
		$revision = ($options['revision']!=null)?' -r' . $options['revision']:'';
		if (is_dir($exportDir)) {
			// svn update
			$command = "svn update" . $revision ." tmpDir 2>&1";
			$output .= $this->executeCommand($command, __('svn update',true), 'export', $exportDir);
			
		} else {			
			// TODO Remove DIRMODE
			if (@mkdir($exportDir, octdec(_DIRMODE), TRUE)) {
				$output .= "-[".__('creating directory', true)." $exportDir]\n";
			} else {
				$this->triggerError("Unable to create directory ".$exportDir." during export step");
				return false;
			}
			
			// Export code from SVN
			$authentication = ' --username ' . $options['user_svn'] . ' --password ' . $options['password_svn'];
			$command = "svn checkout" . $revision . $authentication . " " . $project['Project']['svn_url'] . " tmpDir 2>&1";
			$output .= $this->executeCommand($command, __('svn checkout',true), 'export', $exportDir);
		}

		// Create target dir (if required)
		if (!is_dir($project['Project']['prd_path'])) {
			// TODO Remove DIRMODE
			if (@mkdir($project['Project']['prd_path'], octdec(_DIRMODE), TRUE)) {
				$output .= "-[".__('creating directory', true)." ".$project['Project']['prd_path']."]\n";
			} else {
				$this->triggerError("Unable to create directory ".$project['Project']['prd_path']." during export step");
				return false;
			}
		}
		
		return $output;
	}// export
	
	/**
	 * Step 3 of the deployment process: Synchronize
	 * Synchronize the exported source code from snv with the code located in the target directory
	 * @param array $project 	Project that should be deployed 
	 * @param array $options	Various options used for configuring the step 
	 * @return string 			Shell output 
     */
	private function synchronize($project = null, $options = array()) {
		
		if ( $project == null || !$this->isConfigAvailable($project['Project']['name'])) {
			return false;			
		}
		// Set a time limit
		set_time_limit(_TIMELIMIT_RSYNC);
			
		// Inculde deployment config file
		include_once (_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS . "deploy.php");
			
		// Define step options
		$default_options = array(
			'backup'		=>	true,
			'simulation' 	=> 	true,
			'user'			=> 	'unknown',
			'comment' 		=> 	'none'
		);
		$options = array_merge($default_options, $options);
		$output = '';

		// Generate exclusion file
		$exclude = $this->_getConfig()->exclude;
		$exclude_string = "";
		for ($i = 0; $i < sizeof($exclude); $i++) {
			$exclude_string .= "- ".$exclude[$i] . "\n";			
		}
		$exclude_string .= "- deploy.php\n";
		$exclude_string .= "- **.dev.**\n";
		$exclude_string .= "- **.svn**\n";	
		$exclude_file_name = _DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "exclude_file.txt";
		$handle = fopen($exclude_file_name, "w");
		fwrite($handle, $exclude_string);
		fclose($handle);
			
		// Setting up Rsync options
		if ($options['simulation'] === true) {
			// Simulation mode
			$option = 'rtvn';
		} else {			
			// Live mode
			$option = 'rtv';
			// Create a log entry for the pending deployement 
			$data = array (
				'DeploymentLog' => array (
					'project_id'	=> 	$project['Project']['id'],
					'user_id' 		=> 	$options['user'],
					'uuid'			=> 	$this->uuid,
					'title' 		=> 	date("D, M jS Y, H:i") . ' - ' . $project['Project']['name'],
					'comment' 		=> 	$options['comment'],
					'archive' 		=> 	0
				)
			);
			
			//The rsync option "O" not yet supported on Mac
			if (Configure::read('OS.type') != 'DAR') {
				$option .= 'O';
			}
			
			if (!$this->DeploymentLog->save($data) ) {
				$this->triggerError('Unable to log deployment');				
				return false;				
			}
			
			// Backup (if required)
			if ($options['backup'] === true) {
				if ( ($output .= $this->backup($project)) === false) {
					$this->triggerError('Unable to backup');	
					return false;
				}
			}
		}

		// Execute command		
		$exclude_file_name = self::pathConverter($exclude_file_name);
		$source = self::pathConverter(_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS);
		$target = self::pathConverter($project['Project']['prd_path']);
		$command = "rsync -$option --delete --exclude-from=$exclude_file_name $source $target 2>&1";	
		$output .= $this->executeCommand($command,__('Deploying new version',true), 'synchronize', _DEPLOYDIR);

		// Create files list and directories list for chmod step
		$list = explode("\n", $output);
		$size = count($list);
		if ($size > 0) {
			$files_to_chmod = _DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "files_to_chmod.txt";
			$dir_to_chmod = _DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "dir_to_chmod.txt";
			$handle_f = fopen($files_to_chmod, "w");
			$handle_d = fopen($dir_to_chmod, "w");

			for ($i = 4; $i < $size ; $i++) { 
				if (empty($list[$i])) {
					break;
				}
		
				if (is_file($target . $list[$i])) {
					$tmp_str = $list[$i];
					fwrite($handle_f, $target.str_replace(".prd.", ".", $list[$i]) . "\n");
				} else {
					fwrite($handle_d, $target.$list[$i] . "\n");
				}
			}
			fclose($handle_f);
			fclose($handle_d);
		}
		return $output;
	}// synchronize

	/**
	 * Step 4 of the deployment process: Finalize
	 * @param int $project 		Project that should be deployed 
	 * @param array $options	Various options used for configuring the step 
	 * @return string 			Shell output 
     */
	private function finalize($project = null, $options = array()) {
		if ( $project == null || !$this->isConfigAvailable($project['Project']['name'])) {
			return false;	
		}
		
		// Set a time limit
		set_time_limit(_TIMELIMIT_FINALIZE);
		
		// Inculde deployment config file
		include_once (_DEPLOYTMPDIR.DS.$project['Project']['name'].DS.'tmpDir'.DS.'deploy.php');
			
		// Define step options
		$default_options = array(
			'renamePrdFile' 		=> 	false,
			'changeFileMode' 		=> 	false,
			'giveWriteMode'			=> 	false,
			'modifiedFileOnly'		=> 	false
			
		);
		$options = array_merge($default_options, $options);
		$output = '';
		
		// Rename file type from .prd.xxx into .xxx
		if ($options['renamePrdFile'] === true) {			
			$command = "find " . self::pathConverter($project['Project']['prd_path']) . " -name '*.prd.*' -exec /usr/bin/perl ".self::pathConverter(_DEPLOYDIR)."/renamePrdFile -vf 's/\.prd\./\./i' {} \;";
			$output .= $this->executeCommand($command, __('Rename files', true) . '.prd.', 'finalize', _DEPLOYDIR);
		}

		// Change file mode
		if ($options['changeFileMode'] === true) {			
			
			// change file mode only for modified files
			if ($options['modifiedFileOnly'] === true) {
				$path = _DEPLOYTMPDIR . DS . $project['Project']['name'] . DS;
				$command = "chmod " ._FILEMODE . "  $(<". $path . "files_to_chmod.txt)";
				$output .= $this->executeCommand(
					$command, 
					__('updating files modes', true) . ' > ' . htmlspecialchars($command), 
					'finalize', 
					_DEPLOYDIR
				);
				
				$command = "chmod " ._DIRMODE . "  $(<". $path . "dir_to_chmod.txt)";
				$output .= $this->executeCommand(
					$command, 
					__('updating dir mode', true) . ' > ' . htmlspecialchars($command), 
					'finalize', 
					_DEPLOYDIR
				);
			
			// change file mode on all the project files
			} else {
				$command = "find " . self::pathConverter($project['Project']['prd_path']) . " -type f -exec chmod " . _FILEMODE . " {} \;";
				$output .= $this->executeCommand(
					$command, 
					__('updating files modes', true) . ' > ' . _FILEMODE, 
					'finalize', 
					_DEPLOYDIR
				);
	
				$command = "find " . self::pathConverter($project['Project']['prd_path']) . " -type d -exec chmod " . _DIRMODE . " {} \;";
				$output .= $this->executeCommand($command, __('updating dir mode', true) . '2/2 > ' . _DIRMODE, 'finalize');
			}
		}
		
		// Change directory mode
		if ($options['giveWriteMode'] === true) {
			// Give write permissions to some folder
			$writable = $this->_getConfig()->writable;
			if (sizeof($writable) > 0) {
				for ($i = 0; $i < sizeof($writable); $i++) {
					$command = "chmod -vR "._WRITEMODE."  ".self::pathConverter($project['Project']['prd_path'].$writable[$i] );
					$output .= $this->executeCommand($command, 'Setting write permissions', 'finalize');
				}
			}
		}
		
		return $output;
	}// finalize

	/**
	 * Optional step in the deployment process: Backup
	 * @param array $project 	Project that should be deployed 
	 * @return string 			Shell output 
     */
	private function backup($project) {
		$output = '';
		
		// création du répertoire pour la sauvegarde
		$backupDir =_DEPLOYBACKUPDIR.DS.$project['Project']['name'] ;
		if (!is_dir($backupDir)) {
			if (mkdir($backupDir, octdec(_DIRMODE), TRUE)) {
				$output .= "-[".__('creating directory')." $backupDir]\n";
			} else {
				$this->triggerError("Unable to create directory $backupDir during export step");
				return false;
			}
		}

		$output .= "-[".__('backup current prod version')."]\n";
		if (is_dir($project['Project']['prd_path'])) {
			$source = self::pathConverter($project['Project']['prd_path'] );
			$target = self::pathConverter($backupDir);
		
			// rsync pour le backup
			$command = "rsync -av $source $target 2>&1";
			$output .= $this->executeCommand($command, __('backup current prod version'), 'backup');
			
			$command = "chmod -R "._DIRMODE." "._DEPLOYBACKUPDIR;
			$output .= $this->executeCommand($command, __('updating dir mode') . ' > ' . _DIRMODE, 'backup');
		} else {
			$output .= "-[".__('no backup needed')." ".$project['Project']['prd_path']." ".__('does not exist')."]\n";
		}

		// TODO Check du backup
		return $output;
	}// backup

	/**
	 * Check an exported project contains the deploy.php config file 
	 * @param string $projectName 	Name of the project to be checked
	 * @return boolean  			True or false
	 */ 
	function isConfigAvailable(	$projectName ) {
		$res = @ file_exists(_DEPLOYTMPDIR . DS . $projectName . DS . "tmpDir" . DS . "deploy.php");
		if ($res === false) {
			$this->triggerError("Unable to find 'deploy.php' file");
		}
		return $res;
	}// isConfigAvailable

	function triggerError($error) {
		$this->lastError = $error;
		CakeLog::write(LOG_ERROR, $this->lastError);
	}// triggerError

	/**
	 * Get the execution time of the last operation (step or process) 
	 * @return int 	Last execution time
	 */ 
	function getLastExecutionTime () {
		return $this->lastExecutionTime;
	}//getLastExecutionTime

	/**
	 * Get the error of the last operation (step or process) 
	 * @return int 	Last execution time
	 */ 
	function getLastError () {
		return $this->lastError;
	}//getLastExecutionTime

	/**
	 * Convert if necessary a path to a cygwin/linux format 
	 * @param string $path 		Path to be converted
	 * @return string 			Converted path
	 */ 
	function pathConverter($path) {
		$pathForRsync = $path;
		if ( Configure::read('OS.type') == 'WIN') {
			$pattern = '/^([A-Za-z]):/';
			preg_match($pattern, $path, $matches, PREG_OFFSET_CAPTURE);
			if (!empty ($matches[1][0])) {
				$windowsLetter = strtolower($matches[1][0]);
				$pathForRsync = strtr(Configure::read('OS.Cygwin.rootDir') . $windowsLetter . substr($path, 2), "\\", "/");
			}	
		}
		return $pathForRsync;
	}// pathConverter

	// Private --------------------------------------------------------------------------------
	/**
	 * Retrieve deploy configuration
	 * @return 
	 */
	private function &_getConfig() {
		static $instance;

		if (!isset($instance) || !$instance) {
			$instance = &new DEPLOY_CONFIG();
		}

		return $instance;
	}// _getConfig
	
	function executeCommand( $command = null, $comment = 'none', $context = 'none', $newDir = null ){
		if ($command == null) {
			return __('No command supplied');
		}
		
		if ($newDir != null) {
			chdir($newDir);			
		}
			
		$output = "\n-[".$comment."]\n";
		if ( Configure::read() > 0 ){
			CakeLog::write('debug', "[$context] " . $command);			
		}
		
		if ( Configure::read('OS.type') == 'WIN' ) {
			$prefix = "bash.exe --login -c '";
			$suffix = "'";
		} else {
			$prefix = "";
			$suffix = "";
		}
		$shell = shell_exec( $prefix.$command.$suffix );
		return $output . $shell;
	}// executeCommand
	
}// Deployment
?>