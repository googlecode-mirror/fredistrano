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
	
	var $allowedSteps = array(
		'export',
		'synchronize',
		'finalize',
		'clearProjectTempFiles',
		'resetPermissions'
	);

	// Internal data (private)
	var $_stepLog = null;

	var $_context = array();

	var $_project = null;

	static $_config = null;
	
	// Constructor
	function __construct() {
		parent::__construct();
	
		// Manual links
		App::import('Model','Project');
		$this->Project = new Project(); 
		
		App::import('Model','DeploymentLog');
		$this->DeploymentLog = new DeploymentLog();

		// Custom classes
		App::import('Vendor', 'fbollon/log_classes');
		App::import('Vendor', 'fbollon/commands');
	}// __construct 
	
	// Public deploy -----------------------------------------------------------------		
	/**
	 * Run a complete deployment process
	 * @param int $project_id 		Id of the project that should be deployed 
	 * @param array $options		Various options used for configuring the step 
	 * @return string 				Shell output 
     */
	function process($projectId, $options = array()) {
		$processLog = new Processlog();
		
		try {
			if ( !isset($this->_context['user']) || !isset($this->_context['uuid']) ) { 
				$processLog->error( __('Invalid context (use setContext() first)',true) );
			}
			$processLog->setContext($context['user'], $context['uuid']);
						
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
				FIXME F: Test merge result (use array_merge_recursive rather?)
			*/
			$options = Set::merge($default_options, $options);

			// Running steps
			for ( $i=1 ; $i<count($this->process) ; $i++ ) {
				$step = $this->process[$i];
				$this->_runStep($step, $projectId, $context, $options[$step]);
				$processLog->addChildLog( $this->_stepLog );
				
				if ($step == 'export') {
					// Post actions
					if (!($rev = $this->_stepLog->data['revision'])) {
						$options['comment'] = sprintf( __('Revision exported %s', true), $rev);			
					}	
					$options = Set::merge($options, $this->_config->options);	
				}
			}// for

			// Process result
			$processLog->end();		
				
		} catch (Exception $e) { 
			if ( !$processLog->hasError() ) {
				$processLog->error(__('An error occured during the process. See steps for further details.',true), false);
			}
		}

		return $processLog;
	}// process
	
	// Nicer call to functions
	public function __call($name, $arguments) {
		// Dispatch
		if (!in_array($name, $this->allowedSteps))) {		
			try{	
				if (count($arguments) == 1) {
					$this->_runStep($name,$arguments[0]);
				} else if (count($arguments) == 2) {
					$this->_runStep($name,$arguments[0],$arguments[1]);
				}  else {
					return false;
				}
			} catch (Exception $e) { }
			
			return $this->_stepLog;	
		} 
		
		return false;
	}// __call

	//  Private Steps -----------------------------------------------------------
	/**
	 * Run a deployment step and performs the required checks
	 * @param string $step			Step that should be performed 
	 * @param int $project_id 		Id of the project that should be deployed 
	 * @param array $options		Various options used for configuring the step 
	 * @return StepLog 				Step log  
     */			
	private function _runStep($step, $projectId, $options = array()) {
		$this->_stepLog = new Steplog( $step );
		
		try{	
			// Check input parameters
			if ( !isset($this->_context['user']) || !isset($this->_context['uuid']) ) { 
				$this->_stepLog->error( __('Invalid context (use setContext() first)',true) );
			}
			if ( !in_array($step, $this->$process) ) {
				$this->_stepLog->error( __('Unknown step',true) );
			}
			$this->_stepLog->setContext($context['user'], $context['uuid']);
		
			// Initialiaze processing
			$this->_context = $context;
			$this->_project = $this->Project->find('first', array('conditions' => array('Project.id' => $projectId), 'recursive' => 0);
	 		if ( !$this->_project )  {
				$this->_stepLog->error( __('Unknown project',true) );
			}
		
			// Execute step	
			set_time_limit( Configure::read('Deployment.timelimit.'.$step) );
			$this->{'_'.$step}( $options);		
			$this->_stepLog->end(); // F_DEPLOYLOGDIR.$context['uuid'].'.log' );
			
		} catch (Exception $e) { 
			if ( !$this->_stepLog->hasError() ) {
				$this->_stepLog->error(__('An error occured during the step. See actions for further details.',true));
			} 
		}

	}// _runStep

	/*
		FIXME F: implement mode export instead of checkout 
	*/
	/**
	 * Step 1 of the deployment process: Export
	 * @param SetLog 	$setLog		Custom logging object 
	 * @param array 	$options	Various options used for configuring the step 
     */
	private function _export($options = array()) {				
		// Check input parameters
		if (!$this->isInitialized()) {
			$this->_stepLog->error( sprintf(__('Missing working data', true)) );
		}
		
		// Define step options
		$default_options = array(
			'revision' 		=> 	null,
			'user_svn' 		=> 	Configure::read('Subversion.user'),
			'password_svn' 	=> 	Configure::read('Subversion.passwd')
		);
		$options = array_merge($default_options, $options);

		// Looking for sources
		$projectTmpDir = F_DEPLOYTMPDIR.$this->_project['Project']['name'].DS;
		$revision = ($options['revision']!=null)?' -r' . $options['revision']:'';
		if (is_dir($projectTmpDir)) {
			
			// svn update
			$command = "svn update" . $revision ." tmpDir 2>&1";		
			$log = Command::execute( $command, 
				array(
			        'comment'=>__('SVN update',true),
			        'newDir'=>$projectTmpDir
				)
			);
			$this->_stepLog->addChildLog( $log );		
			
		} else {			
			
			// Create tmpDir folder inside Fredistrano
			$actionLog = $this->_stepLog->addNewAction('create', 'Directory: '.$projectTmpDir, 'FS');
			if (!@mkdir($projectTmpDir, octdec( Configure::read('FileSystem.permissions.directories') ), TRUE)) {
				$actionLog->error( sprintf(__('Unable to create directory %s', true), $projectTmpDir) );
			}
			$actionLog->end();
			
			// Export code from SVN
			$authentication = '';
			if (!empty($options['password_svn'])) {
				$authentication = '--username '.$options['user_svn'].' --password '.$options['password_svn'];
			}
			$command = "svn checkout $revision $authentication ".$this->_project['Project']['svn_url'].' tmpDir 2>&1';
			$log = Command::execute( $command,
				array(
			        'comment'=>__('SVN checkout',true),
			        'newDir'=>$projectTmpDir
				)
			);
			$this->_stepLog->addChildLog( $log );			
		}
		$actionLog = $this->_stepLog->getLastLog();
		$this->_stepLog->data['revision'] = Command::getSvnRevision( $actionLog->getResult() );
		
		// Load project configuration 
		self::_loadConfig();
	}// _export
	
	/**
	 * Step 2 of the deployment process: Synchronize
	 * Synchronize the exported source code from snv with the code located in the target directory
	 * @param SetLog 	$setLog		Custom logging object 
	 * @param array $options	Various options used for configuring the step 
     */
	private function _synchronize($stepLog, $options = array()) {
		// Check input parameters
		if (!$this->isInitialized()) {
			$this->_stepLog->error( sprintf(__('Missing working data', true)) );
		} 
			
		// Load project configuration 
		self::_loadConfig();
			
		// Define step options
		$default_options = array(
			'simulation' 		=> 	true,
	 		'runBeforeScript'	=> 	false,
			'backup'			=>	true,
			'comment' 			=> 	'none'
		);
		$options = array_merge($default_options, $options);
		
		$projectTmpDir = F_DEPLOYTMPDIR.$this->_project['Project']['name'].DS;
		// Synchronize target files
		// Create target dir (if required)
		$actionLog = $this->stepLog->addNewAction('create', 'Directory: '.$this->_project['Project']['prd_path'], 'FS');
		if (!is_dir($this->_project['Project']['prd_path'])) {
			if (!@mkdir($this->_project['Project']['prd_path'], octdec(  Configure::read('FileSystem.permissions.directories') ), TRUE)) {
				$actionLog->error( sprintf(__('Unable to create directory %s', true), $this->_project['Project']['prd_path'])) );
			}
			$actionLog->end();
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
			$actionLog = $this->stepLog->addNewAction('create', 'Directory: '.$this->_project['Project']['prd_path'], 'FS');
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
				$actionLog->error( __('Unable to save deployment log', true) );
			}
			$actionLog->end();
			
			// Run initialization script
			$this->__runScript('before');
			/*
				FIXME F: ReActivate Backup
			*/
			// // Backup (if required)
			// if ($options['backup'] === true) {
			// 	if ( ($output .= $this->_backup()) === false) {
			// 		$this->triggerError(__('Unable to backup', true));	
			// 		return false;
			// 	}
			// }
		}

		// Execute command		
		$excludeFileName = Command::convertPath( $excludeFileName );
		$source = Command::convertPath( $projectTmpDir."tmpDir". DS );
		$target = Command::convertPath( $this->_project['Project']['prd_path'] );
		$command = "rsync -$option --delete --exclude-from=$excludeFileName $source $target 2>&1";	
		$log = Command::execute( $command,
			array(
		        'comment'	=> __('Deploying new version',true),
		        'newDir'	=> F_DEPLOYDIR
			)
		);
		$this->_stepLog->addChildLog( $log );	

		// Create files list and directories list for chmod step
		$actionLog = $this->stepLog->addNewAction('create', 'Contents', 'FS');
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
		$actionLog->end();
		
		return $output;
	}// _synchronize

	/**
	 * Step 3 of the deployment process: Finalize
	 * @param array $options	Various options used for configuring the step 
	 * @return string 			Shell output 
     */
	private function _finalize($options = array()) {
		// Check input parameters
		if (!$this->isInitialized()) {
			$this->_stepLog->error( sprintf(__('Missing working data', true)) );
		}
		
		// Load project configuration 
		self::_loadConfig();
			
		// Define step options
		$default_options = array(
			'renamePrdFile' 		=> 	false,
			'changeFileMode' 		=> 	false,
			'giveWriteMode'			=> 	false,
 			'runAfterScript'		=> 	false
		);
		$options = array_merge($default_options, $options);
		
		$projectTmpDir = F_DEPLOYTMPDIR.$this->_project['Project']['name'].DS;
		// Rename file type from .prd.xxx into .xxx
		if ($options['renamePrdFile'] === true) {			
			$command = "find ".Command::convertPath($this->_project['Project']['prd_path'])." -name '*.prd.*' "
				."-exec /usr/bin/perl ".Command::convertPath(F_DEPLOYDIR)."renamePrdFile -vf 's/\.prd\./\./i' {} \;";
			Command::execute( $command,
				array(
			        'comment'	=> __('Rename .prd files', true),
			        'newDir'	=> F_DEPLOYDIR
				)
			);
			
		}

		// Change file mode
		if ($options['changeFileMode'] === true) {			
			$command = "chmod ".Configure::read('FileSystem.permissions.files')."  $(<".$projectTmpDir."files_to_chmod.txt)";
			$log = Command::execute( $command,
				array(
			        'comment'	=> sprintf(__('Changing files permissions to %s', true), Configure::read('FileSystem.permissions.files')),
				)
			);
			$this->_stepLog->addChildLog( $log );	
			
			$command = "chmod ". Configure::read('FileSystem.permissions.directories')."  $(<". $projectTmpDir . "dir_to_chmod.txt)";
			$log = Command::execute( $command, 
				array(
			        'comment'	=> sprintf(__('Changing directories permissions to %s', true), Configure::read('FileSystem.permissions.directories')),
			);
			$this->_stepLog->addChildLog( $log );	
		}
		
		// Change directory mode
		if ($options['giveWriteMode'] === true) {
			// Give write permissions to some folder
			$writable = $this->_config->writable;
			if (sizeof($writable) > 0) {
				for ($i = 0; $i < sizeof($writable); $i++) {
					$command = "chmod -vR ".Configure::read('FileSystem.permissions.writable')."  "
						.Command::convertPath($this->_project['Project']['prd_path'].$writable[$i] );
					Command::execute( $command, $this->_stepLog, 
						array(
					        'comment'	=> sprintf(__('Changing writeable permissions', true), Configure::read('FileSystem.permissions.writable'))
						)
					);
				}
			}
		}
			
		// Running finalization script
		$this->__runScript('after');

	}// _finalize
	
	private function _clearProjectTempFiles(){
		// Check input parameters
		if (!$this->isInitialized()) {
			$this->_stepLog->error( sprintf(__('Missing working data', true)) );
		}
		
		$path = Command::convertPath(F_DEPLOYTMPDIR.$this->_project['Project']['name'].DS);
		if (!is_dir($path)) {
			$this->_stepLog->error( sprintf(__('No temporary files found', true)) );
		} 			

		$log = Command::execute( $command,
			array(
		        'comment'	=> __('Deleting project temp files', true).' > '.htmlspecialchars($command)
			)
		);
		$this->_stepLog->addChildLog( $log );	
	}// _clearProjectTempFiles
	
	private function _resetPermissions(){
		// Check input parameters
		if (!$this->isInitialized()) {
			$this->_stepLog->error( sprintf(__('Missing working data', true)) );
		}
			
		// Load project configuration 
		self::_loadConfig();
		
		// Change file mode
		$command = "find ".Command::convertPath($this->_project['Project']['prd_path'])." -type f -exec chmod "
						.Configure::read('FileSystem.permissions.files')." {} \;";
		$log = Command::execute( $command,  
			array(
		        'comment'	=> sprintf(__('Resetting files permissions to %s', true), Configure::read('FileSystem.permissions.files')),
				'directory'	=> F_DEPLOYDIR
			)
		);
		$this->_stepLog->addChildLog( $log );	
		
		// Change directory mode
		$command = "find ".Command::convertPath($this->_project['Project']['prd_path'])." -type d "
					."-exec chmod ".Configure::read('FileSystem.permissions.directories')." {} \;";
		$log = Command::execute( $command, 
			array(
		        'comment'	=> sprintf(__('Resetting directories permissions to %s', true), Configure::read('FileSystem.permissions.directories')),
				'directory'	=> F_DEPLOYDIR
			)
		);
		$this->_stepLog->addChildLog( $log );	
		
		// Give write permissions to some folder
		$writable = $this->_config->writable;
		if (sizeof($writable) > 0) {
			for ($i = 0; $i < sizeof($writable); $i++) {
				$command = "chmod -vR ".Configure::read('FileSystem.permissions.writable')."  "
					.Command::convertPath($this->_project['Project']['prd_path'].$writable[$i] );
				$log = Command::execute( $command, 
					array(
				        'comment'	=> sprintf(__('Resetting writeable permissions to %s', true), Configure::read('FileSystem.permissions.writable')),
						'directory'	=> F_DEPLOYDIR
					)
				);
				$this->_stepLog->addChildLog( $log );	
			}
		}
	}// _resetPermissions
	
	// /**
	//  * Optional step in the deployment process: Backup
	//  * @return string 			Shell output 
	//      */
	// private function __backup() {
	// 	if (!$this->isInitialized()) {
	// 		return false;		
	// 	}
	// 	
	// 	$output = '';
	// 	// création du répertoire pour la sauvegarde
	// 	$backupDir = F_DEPLOYBACKUPDIR.$this->_project['Project']['name'] ;
	// 	if (!is_dir($backupDir)) {
	// 		if (mkdir($backupDir, octdec( Configure::read('FileSystem.permissions.directories')), TRUE)) {
	// 			$output .= "-[".__('creating directory')." $backupDir]\n";
	// 		} else {
	// 			$this->triggerError(sprintf(__('Unable to create directory %s',true), $backupDir));
	// 			return false;
	// 		}
	// 	}
	// 
	// 	$output .= "-[".__('backup current prod version')."]\n";
	// 	if (is_dir($project['Project']['prd_path'])) {
	// 		$source = self::pathConverter($project['Project']['prd_path'] );
	// 		$target = self::pathConverter($backupDir);
	// 	
	// 		// rsync pour le backup
	// 		$command = "rsync -av $source $target 2>&1";
	// 		$output .= $this->executeCommand($command, __('backup current prod version'), 'backup');
	// 		
	// 		$command = "chmod -R ". Configure::read('FileSystem.permissions.directories')." ".F_DEPLOYBACKUPDIR;
	// 		$output .= $this->executeCommand($command, __('updating dir mode') . ' > '. Configure::read('FileSystem.permissions.directories'), 'backup');
	// 	} else {
	// 		$output .= "-[".__('no backup needed')." ".$project['Project']['prd_path']." ".__('does not exist')."]\n";
	// 	}
	// 
	// 	/*
	// 		TODO F: Check backup
	// 	*/
	// 	return $output;
	// }// backup
	
	private function __runScript( $type = 'before' ) {
		$projectTmpDir = F_DEPLOYTMPDIR.$this->_project['Project']['name'].DS;
		
		// Run before script
		if ($options['run'.ucfirst($type).'Script']) {			
			$scriptPath = $this->_config->scripts[$type];
			if (!file_exists($scriptPath) && file_exists($projectTmpDir.'tmpDir'.DS.'.fredistrano'.DS.$scriptPath)) {
				$scriptPath = $projectTmpDir.'tmpDir'.DS.'.fredistrano'.DS.$scriptPath;
			} else if (!file_exists($scriptPath)){
				$this->_stepLog->error( __('Script not found', true) );
			}

			if (!is_executable($scriptPath)) {
				$log = Command::execute( "chmod u+x $scriptPath", 
					array(
				        'comment'=>__('Execution privileges to script',true)
					)
				);	
				$this->_stepLog->addChildLog( $log );		
			}
			Command::execute( $scriptPath,
				array(
			        'comment'	=> sprintf(__('%s script',true), $type)
				)
			);
			$this->_stepLog->addChildLog( $log );	
		}
	}// __runScript
	
    // Public methods ---------------------------------------------------------
	public function isInitialized() {
		if ( is_null($this->_project) || is_null($this->_context) ||  is_null($this->_stepLog)) {
			return false;		
		} else {
			return true;
		}
	}// isInitialized

	/**
	 * Generate a unique deployment id for a given project or the current one   
	 * @param string $id		Project id; if null, the id of the currently processed project is used
	 * @return string 			Some kind of UUID
	 */ 
	function generateUuid ( $projectId = null ) {
		if (is_null($projectId) && !is_null($this->_project) && isset($this->_project['Project']['id'])) {
			 $projectId = $this->_project['Project']['id'];
		}
		return md5( 'FREDISTRANO:'.$projectId .':'.time() ); 
	}// generateUuid	

	/*
		TODO F: Add project as parameter
	*/
	public function getConfig() {
		try {
			self::_loadConfig();	
		} catch (Exception $e) {
			return false;
		}
		return $this->_config;
	}// getConfig
	
	public function setContext($context) {
		$this->_context = $context;
	}// setContext

    // Helper functions ( private ) ---------------------------------------------------------
	private function _loadConfig() {
		$actionLog = $this->stepLog->addNewAction('loadConfig', null,'include');
		
		if (!isset($this->_project) || !$this->_project) {
			$actionLog->error( sprintf(__('Missing working data', true)) );
		}
		
		if (!isset($this->_config) || !$this->_config) {
			// Check new path
			$path = $this->__getConfigPath(true, $this->_project['Project']['name']);
			if ( !file_exists( $path ) ) {
				$path = $this->__getConfigPath(false, $this->_project['Project']['name']);
				if (!file_exists( $path )) {
					$actionLog->error( sprintf(__('Unable to find deploy.php', true)) );
				}
			} 
			include_once($path);
			$this->_config = &new DEPLOY_CONFIG();
		}

		$actionLog->end();
	}// loadConfig

	/**
	 *
	 */ 
	private function __getConfigPath ($newPath = false, $projectName = null) {
		if ($newPath) {
			return F_DEPLOYTMPDIR.$projectName.DS.'tmpDir'.DS.'.fredistrano'.DS.'deploy.php';
		} else {
			return F_DEPLOYTMPDIR.$projectName.DS.'tmpDir'.DS.'deploy.php';			
		}
	}// getConfigPath

}// Deployment
?>