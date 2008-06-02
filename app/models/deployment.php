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
	function runProcess($project_id = null, $options = array()) {
		if ( $project_id == null || !($project = $this->Project->read(null, $project_id)) ) { 
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

		// Performs successively each step
		//$output = "> Initialize:\n";
		//$output .= $this->initialize($project, $options);
		
		$output = "> Export:\n";
		$output .= $this->export($project, $options);
		preg_match('/ ([0-9]+)\.$/', $output, $matches);
		$options['comment'] = 'Revision exported ' . $matches[1];	

		$output = "> Synchronize:\n";
		$output .= $this->synchronize($project, $options);

		$output = "> Finalize:\n";
		$output .= $this->finalize($project, $options);
		
		$this->lastExecutionTime = round((getMicrotime() - $t1) , 3);
		
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
		$header = date('Y-m-d H:i:s') . " Output of " . strtoupper($step);
		$filename = _DEPLOYLOGDIR . DS . $uuid . '.log';
		clearstatcache(); // Prevent pb with is_dir() function (see PHPDoc)
		if (!is_dir(_DEPLOYLOGDIR)) {
			if (@mkdir(_DEPLOYLOGDIR, octdec(_DIRMODE), TRUE)) {
				$output .= "-[".__('creating directory', true)." " . _DEPLOYLOGDIR . "]\n";
			} else {
				$this->triggerError( "Unable to create directory "._DEPLOYLOGDIR );
				return false;
			}
		}
		$log = new File($filename, true);

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
			$log->append($header." executed in ".$this->lastExecutionTime."s\n<<<<\n".$text."\n>>>>\n");
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
			'revision' 	=> 	null,
			'user' 		=> 	Configure::read('Subversion.user'),
			'password' 	=> 	Configure::read('Subversion.passwd')
		);
		$options = array_merge($default_options, $options);
		$output = '';
		
		// Create temporary folders for Fredistrano (if required)
		if (!is_dir(_DEPLOYDIR)) {
			if (@mkdir(_DEPLOYDIR, octdec(_DIRMODE), TRUE)) {
				$output .= "-[".__('creating directory', true)." " . _DEPLOYDIR . "]\n";
			} else {
				$this->triggerError( "Unable to create directory "._DEPLOYDIR." during export step" );
				return false;
			}
		}
		if (!is_dir(_DEPLOYTMPDIR)) {
			if (@mkdir(_DEPLOYTMPDIR, octdec(_DIRMODE), TRUE)) {
				$output .= "-[".__('creating directory', true)." " . _DEPLOYTMPDIR . "]\n";				
			} else {
				$this->triggerError( "Unable to create directory "._DEPLOYTMPDIR." during export step");
				return false;
			}
		}
		if (!is_dir(_DEPLOYBACKUPDIR)) {
			if (@mkdir(_DEPLOYBACKUPDIR, octdec(_DIRMODE), TRUE)) {
				$output .= "-[".__('creating directory', true). " " . _DEPLOYBACKUPDIR . "]\n";
			} else {
				$this->triggerError( "Unable to create directory "._DEPLOYBACKUPDIR." during export step");
				return false;
			}
		}
		
		// Create temporary folders for the current project (if required)
		if (is_dir(_DEPLOYTMPDIR . DS . $project['Project']['name'])) {
			// IF exists THEN cleared
			$command = 'rm -rf ' . _DEPLOYTMPDIR . DS . $project['Project']['name'] . "/*";
			$output .= $this->executeCommand($command, __('delete directory', true)." " . _DEPLOYTMPDIR . DS . $project['Project']['name'],'export');
		} else {
			// ELSE created
			if (@mkdir(_DEPLOYTMPDIR . DS . $project['Project']['name'], octdec(_DIRMODE), TRUE)) {
				$output .= "-[".__('creating directory', true). " " . _DEPLOYTMPDIR . DS . $project['Project']['name'] . "]\n";
			} else {
				$this->triggerError("Unable to create directory "._DEPLOYTMPDIR . DS . $project['Project']['name']." during export step");
				return false;
			}
		}

		// Create target dir (if required)
		if (!is_dir($project['Project']['prd_path'])) {
			if (@mkdir($project['Project']['prd_path'], octdec(_DIRMODE), TRUE)) {
				$output .= "-[".__('creating directory', true). " " . $project['Project']['prd_path'] . "]\n";
			} else {
				$this->triggerError("Unable to create directory ".$project['Project']['prd_path']." during export step");
				return false;
			}
		}
		
		// Export code from SVN
		$revision = ($options['revision']!=null)?' -r ' . $options['revision']:'';
		$authentication = ' --username ' . $options['user'] . ' --password ' . $options['password'];
		$command = "svn export" . $revision . $authentication . " " . $project['Project']['svn_url'] . " tmpDir 2>&1";
		$output .= $this->executeCommand($command, __('svn export',true),'export', _DEPLOYTMPDIR . DS . $project['Project']['name']);
		
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
		$exclude_file_name = _DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "exclude_file.txt";
		$handle = fopen($exclude_file_name, "w");
		fwrite($handle, $exclude_string);
		fclose($handle);
			
		// Setting up Rsync options
		if ($options['simulation'] === true) {
			// Simulation mode
			$option = 'rtvn'; //'rtOvn';
		} else {			
			// Live mode
			$option = 'rtv'; //'rtOv'; 

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
		$exclude_file_name = $this->_pathConverter($exclude_file_name);
		$source = $this->_pathConverter(_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS);
		$target = $this->_pathConverter($project['Project']['prd_path']);
		$command = "rsync -$option --delete --exclude-from=$exclude_file_name $source $target 2>&1";	
		$output .= $this->executeCommand($command,__('Deploying new version',true), 'synchronize', _DEPLOYDIR);
		
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
		include_once (_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS . "deploy.php");
			
		// Define step options
		$default_options = array(
			'renamePrdFile' 	=> 	false,
			'changeFileMode' 	=> 	false,
			'giveWriteMode'		=> 	false
		);
		$options = array_merge($default_options, $options);
		$output = '';
		
		// Rename file type from .prd.xxx into .xxx
		if ($options['renamePrdFile'] === true) {
			$command = $prefix."find " . $this->_pathConverter($project['Project']['prd_path']) . " -name '*.prd.*' -exec /usr/bin/perl ".$this->_pathConverter(_DEPLOYDIR)."/renamePrdFile -vf 's/\.prd\./\./i' {} \;".$suffix;
			$output .= $this->executeCommand($command, __('Rename files') . '.prd.', 'finalize', _DEPLOYDIR);
		}

		// Change file mode
		// TODO Rewrite code (Too slow) 
		if ($options['changeFileMode'] === true) {
			$command = "chmod -R " ._FILEMODE . "  ".$this->_pathConverter($project['Project']['prd_path']);
			$output .= $this->executeCommand($command, __('updating files modes') . ' > ' . _FILEMODE, 'finalize', _DEPLOYDIR);
						
			$command = "chmod " ._DIRMODE . "  ".$this->_pathConverter($project['Project']['prd_path']);
			$output .= $this->executeCommand($command, __('updating dir mode') . '1/2 > ' . _DIRMODE, 'finalize', _DEPLOYDIR);
						
			$command = $prefix."find " . $this->_pathConverter($project['Project']['prd_path']) . " -type d -exec chmod " . _DIRMODE . " {} \;".$suffix;
			$output .= $this->executeCommand($command, __('updating dir mode') . '2/2 > ' . _DIRMODE, 'finalize');
		}
		
		// Change directory mode
		// TODO Rewrite code (Too slow) 
		if ($options['giveWriteMode'] === true) {
			// Give write permissions to some folder
			$writable = $this->_getConfig()->writable;
			if (sizeof($writable) > 0) {
				for ($i = 0; $i < sizeof($writable); $i++) {
					$command = "chmod -vR " ._WRITEMODE . "  ".$this->_pathConverter($project['Project']['prd_path'] . $writable[$i] );
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
		
		if (!is_dir(_DEPLOYBACKUPDIR)) {
			if (mkdir(_DEPLOYBACKUPDIR, octdec(_DIRMODE), TRUE))
				$output .= "-[".__('creating directory'). " " . _DEPLOYBACKUPDIR . "]\n";
		}

		// création du répertoire pour la sauvegarde
		if (!is_dir(_DEPLOYBACKUPDIR . DS . $project['Project']['name'])) {
			if (mkdir(_DEPLOYBACKUPDIR . DS . $project['Project']['name'], octdec(_DIRMODE), TRUE)) {
				$output .= "-[".__('creating directory'). " " . _DEPLOYBACKUPDIR . DS . $project['Project']['name'] . "]\n";
			}
		}

		//
		$output .= "-[".__('backup current prod version')."]\n";
		if (is_dir($project['Project']['prd_path'])) {
			$source = $this->_pathConverter($project['Project']['prd_path'] );
			$target = $this->_pathConverter(_DEPLOYBACKUPDIR . DS . $project['Project']['name']);
		
			// rsync pour le backup
			$command = "rsync -av $source $target 2>&1";
			$output .= $this->executeCommand($command, __('backup current prod version'), 'backup');
			
			$command = "chmod -R " . _DIRMODE . " " . _DEPLOYBACKUPDIR;
			$output .= $this->executeCommand($command, __('updating dir mode') . ' > ' . _DIRMODE, 'backup');
		} else {
			$output .= "-[".__('no backup needed')." " . $project['Project']['prd_path'] . " ".__('does not exist')."]\n";
		}

		if (is_dir(_DEPLOYBACKUPDIR . DS . $project['Project']['name'])) {
			return $output;
		} else {
			return false;
		}
	}// backup

	/**
	 * Check an exported project contains the deploy.php config file 
	 * @param string $projectName 	Name of the project to be checked
	 * @return boolean  			True or false
	 */ 
	function isConfigAvailable(	$projectName ) {
		$res = @ file_exists(_DEPLOYTMPDIR . DS . $projectName . DS . "tmpDir" . DS . "deploy.php");
		if ($res === false)
			$this->lastError = "Unable to find 'deploy.php' file";
		return $res;
	}// isConfigAvailable

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

	// Private --------------------------------------------------------------------------------
	/**
	 * Retrieve deploy configuration
	 * @return 
	 */
	private function &_getConfig() {
		static $instance;

		if (!isset($instance) || !$instance)
			$instance = &new DEPLOY_CONFIG();

		return $instance;
	}// _getConfig
	
	/**
	 * Convert if necessary a path to a cygwin/linux format 
	 * @param string $path 		Path to be converted
	 * @return string 			Converted path
	 */ 
	private function _pathConverter($path) {
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
	}// _pathConverter
	
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
	
	
	function triggerError($error) {
		$this->lastError = $error;
		CakeLog::write(LOG_ERROR, $this->lastError);
	}// triggerError
	
}// Deployment
?>