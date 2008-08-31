<?php
class Deployment extends AppModel {

	var $name = 'Deployment';

	var $Project = null;
	
	var $DeploymentLog = null;
	
	var $useTable = false;
	
	var $process = array(
		0 => 'export',
		1 => 'synchronize',
		2 => 'finalize'
	);
	
	//  Processing info (public)
	var $lastExecutionTime = 0;
	
	var $lastError = '';	

	// Internal data (private)
	var $_context = array();

	var $_project = null;

	static $_config = null;
	
	// Constructor
	function __construct() {
		parent::__construct();
		
		App::import('Model','Project');
		$this->Project = new Project(); 
		
		App::import('Model','DeploymentLog');
		$this->DeploymentLog = new DeploymentLog();

		if ( Configure::read() > 0 ) {
			if (!class_exists('CakeLog')) {
				uses('cake_log');	
			}			
		}
	}// __construct 
	
	// Public deploy -----------------------------------------------------------------		
	/**
	 * Run a complete deployment process
	 * @param int $project_id 		Id of the project that should be deployed 
	 * @param array $context		Context info about the current deployment 	
	 * @param array $options		Various options used for configuring the step 
	 * @return string 				Shell output 
     */
	/*
		TODO F: Rewrite runProcess (automatic)
	*/	
	function runProcess($project_id = null, $context = array(), $options = array()) {
		if ( $project_id == null || !isset($context['uuid']) ) { 
			$this->lastExecutionTime = 0;
			$this->triggerError('Bad input parameters for runProcess');
			return false;		
		}
				
		// Track time
		$t1 = getMicrotime();
		
		// Init options
		$default_options = array(
	 		'export' 		=> array(),
	 		'synchronize'	=> array(
	 		 	'runBeforeScript'		=> 	false,
	 			'backup'				=> 	false
	 		),
	 		'finalize'		=> array(
		 		'renamePrdFile' 		=> 	false,
				'changeFileMode' 		=> 	false,
				'giveWriteMode'			=> 	false,
	 			'runAfterScript'		=> 	false
	 		)
	 	);
		/*
			TODO F: Test merge result (use array_merge_recursive rather?)
		*/
		$options = Set::merge($default_options, $options);
	
		// Prepare log output
		$log = new File( F_DEPLOYLOGDIR.$context['uuid'].'.log', true );
		$header = date('Y-m-d H:i:s')." Performing fast deploy\n";
		if ($log->writable()) {
			$log->append($header);
		}
		
		$output = '';
		// Running export step 
		$output .= "\n> Performing step Export:\n";
		if ($shell = $this->runStep('export', $project_id, $context, $options['export'])) {
			$output .= $shell;
			/*
				TODO F: SVN pregmatch routine not dry 
			*/
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
		
		// Load project configuration 
		if (!self::loadConfig()) {
			return false;
		}
		$options = Set::merge($options, $this->_config->options);
		
		// Running synchornize step 
		$output .= "\n> Performing step Synchronize:\n";
		if ($shell = $this->runStep('synchronize', $project_id, $context, $options['synchronize'])) {
			$output .= $shell;
		} else {
			if ($log->writable()) {
				$log->append("Process aborted (see error.log for further details)");
			}
			return false;
		}
		
		// Running finalize step
		$output .= "\n> Performing step Finalize:\n";
		if ($shell = $this->runStep('finalize', $project_id, $context, $options['finalize'])) {
			$output .= $shell;
		} else {
			if ($log->writable()) {
				$log->append("Process aborted (see error.log for further details)");
			}
			return false;
		}
		
		// Track time
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
	 * @param array $context		Context info about the current deployment
	 * @param array $options		Various options used for configuring the step 
	 * @return string 				Shell output 
     */			
	function runStep($step = null, $project_id = null, $context = array(), $options = array()) {
		if ( ( $step == null && !in_array($step, $this->$process) ) || $project_id == null || !isset($context['uuid']) ) {
			$this->lastExecutionTime = 0;
			$this->triggerError('Bad input parameters for runStep');
			return false;
		}
		
		// Prepare log output
		$log = new File( F_DEPLOYLOGDIR.$context['uuid'].'.log', true );
		$header = date('Y-m-d H:i:s')." Output of ".strtoupper($step)."\n";
		if ($log->writable()) {
			$log->append($header);
		}
		
		// Initialiaze processing
		$this->_context = $context;
		$this->_project = $this->Project->find('first', array('conditions' => array('Project.id' => $project_id), 'recursive' => 0);
		
		// Execute step
 		if ( !$this->_project )  {
			$this->lastExecutionTime = 0;
			$text = 'Unknown project';
			$this->triggerError($text);
			$output = false;
		} else {
			$startTime = getMicrotime();
			set_time_limit( Configure::read('Deployment.timelimit.'.$step) );
			$text = $output = $this->{'_'.$step}($options);
			$this->lastExecutionTime = round((getMicrotime() - $startTime) , 3);
		} 
		
		// Log text
		if ($log->writable()) {
			$log->append("<<<<\n".$text."\n>>>> Step executed in ".$this->lastExecutionTime."s\n");
		}
		
		return $output;
	}// runStep
		
	//  Private Steps -----------------------------------------------------------
	/**
	 * Step 1 of the deployment process: Export
	 * @param array $options	Various options used for configuring the step 
	 * @return string 			Shell output 
     */
	private function _export($options = array()) {
		if (is_null($this->_project) || is_null($this->_context)) {
			$this->triggerError('Missing working data');
			return false;
		}
		
		// Define step options
		$default_options = array(
			'revision' 		=> 	null,
			'user_svn' 		=> 	Configure::read('Subversion.user'),
			'password_svn' 	=> 	Configure::read('Subversion.passwd')
		);
		$options = array_merge($default_options, $options);
		
		$output = '';
		// Create temporary folders for the current project (if required)
		
		$projectTmpDir = F_DEPLOYTMPDIR.$this->_project['Project']['name'].DS;
		$revision = ($options['revision']!=null)?' -r' . $options['revision']:'';
		if (is_dir($exportDir)) {
			// svn update
			$command = "svn update" . $revision ." tmpDir 2>&1";
			$output .= $this->executeCommand($command, __('svn update', true), 'export', $projectTmpDir);
			
		} else {			
			if (@mkdir($projectTmpDir, octdec( self::dirMode() ), TRUE)) {
				$output .= "-[".__('creating directory', true)." $projectTmpDir]\n";
			} else {
				$this->triggerError(sprintf(__('Unable to create directory %s', true), $projectTmpDir));
				return false;
			}
			
			// Export code from SVN
			$authentication = '';
			if (!empty($options['password_svn'])) {
				$authentication = '--username '.$options['user_svn'].' --password '.$options['password_svn'];
			}
			$command = "svn checkout $revision $authentication ".$this->_project['Project']['svn_url'].' tmpDir 2>&1';
			$output .= $this->executeCommand($command, __('svn checkout',true), 'export', $projectTmpDir);
		}
		
		return $output;
	}// export
	
	/**
	 * Step 2 of the deployment process: Synchronize
	 * Synchronize the exported source code from snv with the code located in the target directory
	 * @param array $options	Various options used for configuring the step 
	 * @return string 			Shell output 
     */
	private function _synchronize($options = array()) {
		if ( is_null($this->_project) || is_null($this->_context)) {
			$this->triggerError('Missing working data');
			return false;		
		}
			
		// Load project configuration 
		if (!self::loadConfig()) {
			return false;
		}
			
		// Define step options
		$default_options = array(
			'simulation' 		=> 	true,
	 		'runBeforeScript'	=> 	false,
			'backup'			=>	true,
			'comment' 			=> 	'none'
		);
		$options = array_merge($default_options, $options);
		
		$output = '';
		$projectTmpDir = F_DEPLOYTMPDIR.$this->_project['Project']['name'].DS;
		// Synchronize target files
		// Create target dir (if required)
		if (!is_dir($this->_project['Project']['prd_path'])) {
			if (@mkdir($this->_project['Project']['prd_path'], octdec( self::dirMode() ), TRUE)) {
				$output .= '-['.__('creating directory', true).' '.$this->_project['Project']['prd_path'].']\n';
			} else {
				$this->triggerError(sprintf(__('Unable to create directory %s', true), $this->_project['Project']['prd_path']));
				return false;
			}
		}
		
		// Generate exclusion file
		$exclude = $this->_config->exclude;
		$excludeString = "";
		for ($i = 0; $i < sizeof($exclude); $i++) {
			$excludeString .= "- ".$exclude[$i] . "\n";			
		}
		$excludeString .= "- deploy.php\n";
		$excludeString .= "- .fredistrano\n";
		$excludeString .= "- **.dev.**\n";
		$excludeString .= "- **.svn**\n";	
		$excludeFileName = $projectTmpDir."exclude_file.txt";
		$handle = fopen($excludeFileName, "w");
		fwrite($handle, $excludeString);
		fclose($handle);
			
		// Setting up Rsync options
		if ($options['simulation'] === true) {
			// Simulation mode
			$option = 'rtvn';
		} else {			
			// Live mode
			$option = 'rtv';
			
			//The rsync option "O" not yet supported on Mac
			if ( F_OS != 'DAR') {
				$option .= 'O';
			}
			
			// Create a log entry for the pending deployement 
			$data = array (
				'DeploymentLog' => array (
					'project_id'	=> 	$this->_project['Project']['id'],
					'user_id' 		=> 	$this->_context['user'],
					'uuid'			=> 	$this->_context['uuid'],
					'title' 		=> 	date("D, M jS Y, H:i") . ' - ' . $this->_project['Project']['name'],
					'comment' 		=> 	$options['comment'],
					'archive' 		=> 	0
				)
			);
			if (!$this->DeploymentLog->save($data) ) {
				$this->triggerError(__('Unable to log deployment', true));				
				return false;				
			}
			
			// Run before script
			if ($options['runBeforeScript']) {			
				$scriptPath = $this->_config->scripts['before'];
				if (!file_exists($scriptPath) && file_exists($projectTmpDir.'tmpDir'.DS.'.fredistrano'.DS.$scriptPath)) {
					$scriptPath = $projectTmpDir.'tmpDir'.DS.'.fredistrano'.DS.$scriptPath;
				} else if (!file_exists($scriptPath)){
					$this->triggerError(__('Script not found',true));
					return false;
				}

				if (!is_executable($scriptPath)) {
					$output .= $this->executeCommand(
						"chmod u+x $scriptPath", 
						__('adding execution privilege to script',true), 
						'export');	
				}
				$output .= $this->executeCommand($scriptPath, __('running initialization script',true), 'export');	
			}
			
			// Backup (if required)
			if ($options['backup'] === true) {
				if ( ($output .= $this->_backup()) === false) {
					$this->triggerError(__('Unable to backup', true));	
					return false;
				}
			}
		}

		// Execute command		
		$excludeFileName = self::pathConverter($excludeFileName);
		$source = self::pathConverter($projectTmpDir."tmpDir". DS);
		$target = self::pathConverter($this->_project['Project']['prd_path']);
		$command = "rsync -$option --delete --exclude-from=$excludeFileName $source $target 2>&1";	
		$output .= $this->executeCommand($command,__('Deploying new version',true), 'synchronize', F_DEPLOYDIR);

		// Create files list and directories list for chmod step
		$list = explode("\n", $output);
		$size = count($list);
		if ($size > 0) {
			$files_to_chmod = $projectTmpDir."files_to_chmod.txt";
			$dir_to_chmod = $projectTmpDir."dir_to_chmod.txt";
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
	 * Step 3 of the deployment process: Finalize
	 * @param array $options	Various options used for configuring the step 
	 * @return string 			Shell output 
     */
	private function _finalize($options = array()) {
		if ( is_null($this->_project) || is_null($this->_context)) {
			$this->triggerError('Missing working data');
			return false;
		}
		
		// Load project configuration 
		if (!self::loadConfig()) {
			return false;
		}
			
		// Define step options
		$default_options = array(
			'renamePrdFile' 		=> 	false,
			'changeFileMode' 		=> 	false,
			'giveWriteMode'			=> 	false,
 			'runAfterScript'		=> 	false
		);
		$options = array_merge($default_options, $options);
		
		$output = '';
		$projectTmpDir = F_DEPLOYTMPDIR.$this->_project['Project']['name'].DS;
		// Rename file type from .prd.xxx into .xxx
		if ($options['renamePrdFile'] === true) {			
			$command = "find ".self::pathConverter($this->_project['Project']['prd_path'])." -name '*.prd.*' "
				."-exec /usr/bin/perl ".self::pathConverter(_DEPLOYDIR)."/renamePrdFile -vf 's/\.prd\./\./i' {} \;";
			$output .= $this->executeCommand($command, __('Rename files', true).'.prd.', 'finalize', F_DEPLOYDIR);
		}

		// Change file mode
		if ($options['changeFileMode'] === true) {			
			$command = "chmod ".Configure::read('FileSystem.permissions.default')."  $(<".$projectTmpDir."files_to_chmod.txt)";
			$output .= $this->executeCommand(
				$command, 
				__('updating files modes', true).' > '.htmlspecialchars($command), 
				'finalize'
			);
			
			$command = "chmod ".self::dirMode()."  $(<". $projectTmpDir . "dir_to_chmod.txt)";
			$output .= $this->executeCommand(
				$command, 
				__('updating dir mode', true) . ' > ' . htmlspecialchars($command), 
				'finalize'
			);
		}
		
		// Change directory mode
		if ($options['giveWriteMode'] === true) {
			// Give write permissions to some folder
			$writable = $this->_config->writable;
			if (sizeof($writable) > 0) {
				for ($i = 0; $i < sizeof($writable); $i++) {
					$command = "chmod -vR ".Configure::read('FileSystem.permissions.writable')."  "
						.self::pathConverter($this->_project['Project']['prd_path'].$writable[$i] );
					$output .= $this->executeCommand($command, 'Setting write permissions', 'finalize');
				}
			}
		}
		
		if ($options['runAfterScript']) {
			$scriptPath = $this->_config->scripts['after'];
			if (!file_exists($scriptPath) && file_exists($projectTmpDir.'.fredistrano'.DS.$scriptPath)) {
				$scriptPath = $projectTmpDir.'.fredistrano'.DS.$scriptPath;
			} else if (!file_exists($scriptPath)){
				$this->triggerError(__('Script not found',true));
				return false;
			}
			
			if (!is_executable($scriptPath)) {
				$output .= $this->executeCommand(
					"chmod u+x $scriptPath", 
					__('adding execution privilege to script',true), 
					'export');	
			}
			$output .= $this->executeCommand($scriptPath, __('running finalization script',true), 'export');	
		}
		
		return $output;
	}// finalize

	/**
	 * Optional step in the deployment process: Backup
	 * @return string 			Shell output 
     */
	/*
		TODO F: Backup not fully tested
	*/
	private function _backup() {
		$output = '';
		// création du répertoire pour la sauvegarde
		$backupDir = F_DEPLOYBACKUPDIR.$project['Project']['name'] ;
		if (!is_dir($backupDir)) {
			if (mkdir($backupDir, octdec(self::dirMode()), TRUE)) {
				$output .= "-[".__('creating directory')." $backupDir]\n";
			} else {
				$this->triggerError(sprintf(__('Unable to create directory %s',true), $backupDir));
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
			
			$command = "chmod -R ".self::dirMode()." ".F_DEPLOYBACKUPDIR;
			$output .= $this->executeCommand($command, __('updating dir mode') . ' > '.self::dirMode(), 'backup');
		} else {
			$output .= "-[".__('no backup needed')." ".$project['Project']['prd_path']." ".__('does not exist')."]\n";
		}

		/*
			TODO F: Check backup
		*/
		return $output;
	}// backup

    // Helper functions ---------------------------------------------------------
	function getConfig() {
		self::loadConfig();
		return $this->_config;
	}

	/**
	 *
	 */ 
	function getConfigPath ($newPath = false, $projectName = null) {
		if ($newPath) {
			return F_DEPLOYTMPDIR.$projectName.DS.'tmpDir'.DS.'.fredistrano'.DS.'deploy.php';
		} else {
			return F_DEPLOYTMPDIR.$projectName.DS.'tmpDir'.DS.'deploy.php';			
		}
	}// getConfigPath

	function loadConfig() {
		if (!isset($this->_project) || !$this->_project) {
			return false;
		}
		
		if (!isset($this->_config) || !$this->_config) {
			// Check new path
			$path = $this->getConfigPath(true, $this->_project['Project']['name']));
			if ( !file_exists( $path ) ) {
				$path = $this->getConfigPath(false, $this->_project['Project']['name']));
				if (!file_exists( $path )) {
					$this->triggerError("Unable to find 'deploy.php' file");
					return false;
				}
			} 
			include_once($path);
			$this->_config = &new DEPLOY_CONFIG();
		}

		return true;
	}// loadConfig
	
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
	}//g etLastExecutionTime

	/**
	 * Get the error of the last operation (step or process) 
	 * @return int 	Last execution time
	 */ 
	function getLastError () {
		return $this->lastError;
	}// getLastError

	/**
	 *
	 */ 
	function generateUuid () {
		return md5( 'FREDISTRANO:'.$id .':'.time() ); 
	}// generateUuid

	// Exportable  --------------------------------------------------------------------------------	
	/*
		TODO F: Implement dirMode function
	*/
	function dirMode() {
		$fileMode = Configure::read('FileSystem.permissions.default');
		// +1 sauf a/ si 0 alors 0 ; b/ si 7 alors 7
		return '755';
	}// dirMode
	
	function executeCommand( $command = null, $comment = 'none', $context = 'none', $newDir = null ){
		if ($command == null) {
            return __('No command supplied');
        }

        $output = "\n-[".$comment."]\n";
        if ( Configure::read() > 0 ){
            CakeLog::write('debug', "[$context] " . $command);
        }

        if ( F_OS == 'WIN' ) {
            $prefix = "bash.exe --login -c 'cd ".$this->pathConverter($newDir)."; ";
            $suffix = "'";
        } else {
            if ($newDir != null) {
               chdir($newDir);
            }
            $prefix = "";
            $suffix = "";
        }
        $shell = shell_exec( $prefix.$command.$suffix );
        return $output . $shell;
	}// executeCommand
	
	/**
	 * Convert if necessary a path to a cygwin/linux format 
	 * @param string $path 		Path to be converted
	 * @return string 			Converted path
	 */ 
	function pathConverter($path) {
		$pathForRsync = $path;
		if ( F_OS == 'WIN') {
			$pattern = '/^([A-Za-z]):/';
			preg_match($pattern, $path, $matches, PREG_OFFSET_CAPTURE);
			if (!empty ($matches[1][0])) {
				$windowsLetter = strtolower($matches[1][0]);
				$pathForRsync = strtr(Configure::read('Cygwin.rootDir') . $windowsLetter . substr($path, 2), "\\", "/");
			}	
		}
		return $pathForRsync;
	}// pathConverter

}// Deployment
?>