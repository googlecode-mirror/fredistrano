<?php
/* SVN FILE: $Id$ */
/**
 * 
 * 
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			app
 * @subpackage		app.models
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.models
 */
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
		App::import('Vendor', 'fbollon/logs');
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
				$processLog->error( __('Invalid process context (use setContext() first)',true) );
			}
			$processLog->setContext($this->_context);
						
			// Init options
			$defaultOptions = Configure::read('Deployment.options');
			$options = Set::merge($defaultOptions, $options);

			// Running steps
			for ( $i=0 ; $i<count($this->process) ; $i++ ) {
				$step = $this->process[$i];
				$this->_runStep($step, $projectId, $options[$step]);
				$processLog->addChildLog( $this->_stepLog );
				
				if ($step == 'export') {
					// Post actions
					if (!empty($this->_stepLog->data['revision'])) {
						$options['synchronize']['comment'] = sprintf(__('Revision exported %s', true), $this->_stepLog->data['revision']);
					}
					if ( isset($this->_config->options) && is_array($this->_config->options)) {
						$options = Set::merge($options, $this->_config->options);
					}
				}
			}// for
			
			// Process result
			$processLog->end();
				
		} catch (Exception $e) {
			$errorLog = $e->getLog();
			if (get_class($errorLog) == 'StepLog') {
				if (!($errorLog->isAttached())) {
					$processLog->addChildLog($errorLog);
				}
			}
			if (!$processLog->hasError()) {
				$processLog->error($processLog->getLastError(), false);
			}
		}
		
		// Write log
		$processLog->save();
		
		return $processLog;
	}// process
	
	// Nicer call to functions
	public function __call($name, $arguments) {
		// Dispatch
		if (in_array($name, $this->allowedSteps)) {		
			try{	
				if (count($arguments) == 1) {
					$this->_runStep($name,$arguments[0]);
				} else if (count($arguments) == 2) {
					$this->_runStep($name,$arguments[0],$arguments[1]);
				}  else {
					return false;
				}
			} catch (Exception $e) {}
			
			// Write log
			$this->_stepLog->save();
			
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
	 */
	private function _runStep($step, $projectId, $options = array()) {
		$stepLog = new StepLog( $step );
		$this->_stepLog = $stepLog;
		try{	
			// Check input parameters
			if ( !isset($this->_context['user']) || (!isset($this->_context['uuid']) && in_array($step, $this->process)) ) { 
				$stepLog->error( __('Invalid step context (use setContext() first)',true) );
			} 
			$stepLog->setContext($this->_context);
			if ( !in_array($step, $this->allowedSteps) ) {
				$stepLog->error( sprintf(__('Unknown step %s',true), $step) );
			}
		
			// Initialiaze processing
			Configure::write('FileSystem.permissions.directories', Utils::computeDirMode(Configure::read('FileSystem.permissions.files'))  ); 
			$this->_project = $this->Project->find('first', array('conditions' => array('Project.id' => $projectId), 'recursive' => 0));
	 		if (!$this->_project)  {
				$stepLog->error( __('Unknown project',true) );
			}
		
			// Execute step
			set_time_limit( Configure::read('Deployment.timelimit.'.$step) );
			$options['stepLog'] = $stepLog;
			$this->{'_'.$step}($options);
			$stepLog->end();
			
		} catch (LogException $e) {
			$errorLog = $e->getLog();
			if (get_class($errorLog) == 'ActionLog') {
				if (!($errorLog->isAttached())) {
					$stepLog->addChildLog($errorLog);
				}
			}
			if (!$stepLog->hasError()) {
				$stepLog->error($stepLog->getLastError(), false);
			}
			// Propagate the error to upper level (if any)
			throw new LogException($stepLog->getError(), $stepLog);
		}
	}// _runStep

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

		$default_options = array(
			'user_svn' 			=> Configure::read('Subversion.user'),
			'password_svn' 		=> Configure::read('Subversion.passwd'),
			'configDirectory'	=> Configure::read('Subversion.configDirectory'),
			'parseResponse'		=> Configure::read('Subversion.parseResponse')
		);
		$options = array_merge($default_options, $options);
		
		// Looking for sources
		$projectTmpDir = F_DEPLOYTMPDIR.$this->_project['Project']['name'];
		if (!is_dir($projectTmpDir)) {
			// Create tmpDir folder inside Fredistrano
			$log =  ShellAction::createDirectory( 
				$projectTmpDir, 
				Configure::read('FileSystem.permissions.directories'), 
				array('stepLog'	=> $this->_stepLog) 
			);
		}
		
		// Retrieve sources by checkout/update method
		if ($this->_project['Project']['method'] == 1) {
			if ( is_dir($projectTmpDir.DS.'tmpDir') ) {
				// svn update
				$log =  SvnAction::update( $projectTmpDir.DS.'tmpDir'.DS, $options);
			} else {
				// Export code from SVN
				$log =  SvnAction::checkout( $this->_project['Project']['svn_url'], $projectTmpDir, 'tmpDir', $options);
			}
		// Retrieve sources by Export method
		} else {
			// Export code from SVN
			if ( is_dir($projectTmpDir.DS.'tmpDir') ) {
				//Clear temporary folders for the current project if exist
				ShellAction::remove($projectTmpDir.DS.'tmpDir', true, array('stepLog'=>$this->_stepLog));
			}
			$log = SvnAction::export( $this->_project['Project']['svn_url'], $projectTmpDir, 'tmpDir', $options);
		}
		
		// Retrieve revision log 
		$this->_stepLog->data['revision'] = Utils::getSvnRevisionFromOutput( $log->getResult() );
		
		// Load project configuration 
		self::_loadConfig();
	}// _export
	
	/**
	 * Step 2 of the deployment process: Synchronize
	 * Synchronize the exported source code from snv with the code located in the target directory
	 * @param SetLog 	$setLog		Custom logging object 
	 * @param array 	$options	Various options used for configuring the step 
	 */
	private function _synchronize($options = array()) {
		// Check input parameters
		if (!$this->isInitialized()) {
			$this->_stepLog->error( sprintf(__('Missing working data', true)) );
		} 
			
		// Load project configuration 
		self::_loadConfig();
			
		// Define step options
		$default_options = array(
			'simulation' 		=> true,
	 		'runBeforeScript'	=> false,
			'backup'			=> false,
			'comment' 			=> 'none'
		);
		$options = array_merge($default_options, $options);
		
		$projectTmpDir = F_DEPLOYTMPDIR.$this->_project['Project']['name'].DS;
		// Synchronize target files
		// Create target dir (if required)
		if (!is_dir($this->_project['Project']['prd_path'])) {
			// Create tmpDir folder inside Fredistrano
			$log = ShellAction::createDirectory( 
				$this->_project['Project']['prd_path'], 
				Configure::read('FileSystem.permissions.directories'), 
				array('stepLog' 	=> $this->_stepLog) 
			);
		}

		// Run initialization script
		if (!$options['simulation']) {
			// Create a log entry for the pending deployement 
			$actionLog =  $this->_stepLog->addNewAction('create', 'Directory: '.$this->_project['Project']['prd_path'], 'FS');
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
			
			if ($options['runBeforeScript']) {
				$scriptPath = null;
				if (isset($this->_config->scripts['before']) && !empty($this->_config->scripts['before'])) {
					$scriptPath = $this->_config->scripts['before'];
				}
				$log = ShellAction::runScript('before', $projectTmpDir, $scriptPath, $options);
			}
			
			// Backup (if required)
			if ($options['backup'] === true) {
				$options['exclude'] = null;
				$source = $this->_project['Project']['prd_path'];
				$target = F_DEPLOYBACKUPDIR.$this->_project['Project']['name']. DS;
				$log = ShellAction::synchronizeContent( $source, $target, $options);
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

		// Run Rsync
		$target = $this->_project['Project']['prd_path'];
		$options['exclude'] = $excludeFileName;
		$log =  ShellAction::synchronizeContent( $projectTmpDir."tmpDir". DS, $target, $options);
		
		// Create file list
		$output = $log->getResult();
		PhpAction::createFilesListToChmod($output, $projectTmpDir, $target,
			array('stepLog' 	=> $this->_stepLog)
		);
		
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
		// Rename file type (eg. from .prd.xxx into .xxx)
		if ($options['renamePrdFile'] === true) {
			$command = "find ".Utils::formatPath($this->_project['Project']['prd_path'])." -name '*.".Configure::read('FileSystem.renameExt').".*' "
				."-exec /usr/bin/perl ".Utils::formatPath(F_DEPLOYDIR.'scripts'.DS)."renamePrdFile -vf 's/\.".Configure::read('FileSystem.renameExt')."\./\./i' {} \;";
			$log =  ShellAction::executeCommand( $command,
				array(
			        'comment'	=> sprintf(__('Rename .%s files', true),Configure::read('FileSystem.renameExt')),
			        'directory'	=> F_DEPLOYDIR,
					'stepLog' 	=> $this->_stepLog
				)
			);
		}

		// Change file mode
		if ($options['changeFileMode'] === true) {
			$command = "chmod -v ".Configure::read('FileSystem.permissions.files')." $(<".Utils::formatPath($projectTmpDir."files_to_chmod.txt").")";
			$log =  ShellAction::executeCommand( $command,
				array(
			        'comment'	=> sprintf(__('Changing files permissions to %s', true), Configure::read('FileSystem.permissions.files')),
					'stepLog' 	=> $this->_stepLog
				)
			);
			
			$command = "chmod -v ". Configure::read('FileSystem.permissions.directories')." $(<".Utils::formatPath($projectTmpDir."dir_to_chmod.txt").")";
			$log =  ShellAction::executeCommand( $command, 
				array(
			        'comment'	=> sprintf(__('Changing directories permissions to %s', true), Configure::read('FileSystem.permissions.directories')),
					'stepLog' 	=> $this->_stepLog
				)	
			);
		}
		
		// Change directory mode
		if ($options['giveWriteMode'] === true) {
			// Give write permissions to some folder
			$writable = $this->_config->writable;
			if (sizeof($writable) > 0) {
				for ($i = 0; $i < sizeof($writable); $i++) {
					$command = "chmod -vR ".Configure::read('FileSystem.permissions.writable')."  "
						.Utils::formatPath($this->_project['Project']['prd_path'].$writable[$i] );
					ShellAction::executeCommand( $command, 
						array(
					        'comment'	=> sprintf(__('Changing writeable permissions', true), Configure::read('FileSystem.permissions.writable')),
							'stepLog' 	=> $this->_stepLog
						)
					);
				}
			}
		}
		
		// Running finalization script
		if ($options['runAfterScript']) {
			$scriptPath = null;
			if (isset($this->_config->scripts['after']) && !empty($this->_config->scripts['after'])) {
				$scriptPath = $this->_config->scripts['after'];
			}
			$log =  ShellAction::runScript('after', $projectTmpDir, $scriptPath, $options);
		}
	}// _finalize
	
	private function _clearProjectTempFiles(){
		// Check input parameters
		if (!$this->isInitialized()) {
			$this->_stepLog->error( sprintf(__('Missing working data', true)) );
		}
		
		// Removing files
		$path = F_DEPLOYTMPDIR.$this->_project['Project']['name'];
		ShellAction::remove($path, true, array('stepLog'=>$this->_stepLog));
	}// _clearProjectTempFiles
	
	private function _resetPermissions(){
		// Check input parameters
		if (!$this->isInitialized()) {
			$this->_stepLog->error( sprintf(__('Missing working data', true)) );
		}
		
		$path = $this->_project['Project']['prd_path'];
		$mode = Configure::read('FileSystem.permissions.files');
		
		// Default permissions
		ShellAction::changePermissions($path, $mode, array('stepLog'=>$this->_stepLog));
		
		// Writable permissions
		self::_loadConfig();
		if ( isset($this->_config->writable) && is_array($this->_config->writable) ) {
			foreach ($this->_config->writable as $subPath) {
				ShellAction::changePermissions($path.$subPath, 
					array (
						'dir' => Configure::read('FileSystem.permissions.writable')
					), 
					array('stepLog' => $this->_stepLog)
				);
			}
		}
	}// _resetPermissions
	
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
		if (!isset($this->_project) || !$this->_project) {
			$actionLog->error( sprintf(__('Missing working data', true)) );
		}
		
		if (!isset($this->_config) || !$this->_config) {
			PhpAction::loadConfig(
				$this->_config, 
				$this->_project['Project']['name'], 
				array('stepLog' => $this->_stepLog)
			);
		}
	}// _loadConfig

}// Deployment
?>
