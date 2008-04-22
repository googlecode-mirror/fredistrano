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
	
	var $lastExecutionTime = 0;
	
	var $lastError = '';
	
	function __construct() {
		parent::__construct();
		
		loadModel('Project');
		$this->Project = new Project(); 
		
		loadModel('DeploymentLog');
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
	function runStep($step = null, $project_id = null, $options = array()) {
		if ( $step == null && !in_array($step, $this->$process) )
			return false;
			
		$t1 = getMicrotime();

		if ( $project_id == null || !($project = $this->Project->read(null, $project_id)) ) 
			$res = false;
		else 
			$output = $this->{$step}($project, $options);	
			
		$this->lastExecutionTime = round((getMicrotime() - $t1) , 3);
		
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
		if ($project===null) 
			return false;

		set_time_limit(_TIMELIMIT_EXPORT);
				
		$default_options = array(
			'revision' 	=> 	null,
			'user' 		=> 	_SVNUSER,
			'password' 	=> 	_SVNPASS
		);
		$options = array_merge($default_options, $options);
		$output = '';
		
		// Perform the job
		//	on efface le cache de stat() sinon problème avec la fonction is_dir() - voir la doc Php
		clearstatcache();

		// si les répertoires temporaires et backup nécessaires à Fredistrano n'existent pas, on le crée
		if (!is_dir(_DEPLOYDIR)) {
			if (mkdir(_DEPLOYDIR, octdec(_DIRMODE), TRUE))
				$output .= "-[".LANG_CREATINGDIRECTORY." " . _DEPLOYDIR . "]\n";
		}
		if (!is_dir(_DEPLOYTMPDIR)) {
			if (mkdir(_DEPLOYTMPDIR, octdec(_DIRMODE), TRUE))
				$output .= "-[".LANG_CREATINGDIRECTORY." " . _DEPLOYTMPDIR . "]\n";
		}
		if (!is_dir(_DEPLOYBACKUPDIR)) {
			if (mkdir(_DEPLOYBACKUPDIR, octdec(_DIRMODE), TRUE))
				$output .= "-[".LANG_CREATINGDIRECTORY. " " . _DEPLOYBACKUPDIR . "]\n";
		}

		//dossier temporaire d'export SVN pour le projet
		if (is_dir(_DEPLOYTMPDIR . DS . $project['Project']['name'])) {
			// on le vide si il existe
			$command = 'rm -rf ' . _DEPLOYTMPDIR . DS . $project['Project']['name'] . "/*";
			$output .= $this->executeCommand($command, LANG_DUMPDIRECTORY." " . _DEPLOYTMPDIR . DS . $project['Project']['name'],'export');
		} else {
			// on le crée si il n'existe pas
			if (mkdir(_DEPLOYTMPDIR . DS . $project['Project']['name'], octdec(_DIRMODE), TRUE))
				$output .= "-[".LANG_CREATINGDIRECTORY. " " . _DEPLOYTMPDIR . DS . $project['Project']['name'] . "]\n";
		}

		// création du répertoire de l'application si il n'existe pas
		if (!is_dir($project['Project']['prd_path'])) {
			if (@ mkdir($project['Project']['prd_path'], octdec(_DIRMODE), TRUE))
				$output .= "-[".LANG_CREATINGDIRECTORY. " " . $project['Project']['prd_path'] . "]\n";
		}

		//on se place dans le dossier temporaire pour faire le svn export
		chdir(_DEPLOYTMPDIR . DS . $project['Project']['name']);
		if ( $options['revision'] != null ) 
			$revision = ' -r ' . $options['revision'];
		else 
			$revision = '';

		$authentication = ' --username ' . $options['user'] . ' --password ' . $options['password'];
			  
		// svn export
		$command = "svn export" . $revision . $authentication . " " . $project['Project']['svn_url'] . " tmpDir 2>&1";
		$output .= $this->executeCommand($command, LANG_SVNEXPORT,'export');
		
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
		if ( $project == null || !$this->isConfigAvailable($project['Project']['name'])) 
			return false;		

		set_time_limit(_TIMELIMIT_RSYNC);
			
		include_once (_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS . "deploy.php");
			
		$default_options = array(
			'backup'		=>	true,
			'simulation' 	=> 	true,
			'comment' 		=> 	'none'
		);
		$options = array_merge($default_options, $options);
		$output = '';

		// Perform the job 
		// Generate exclusion file
		$exclude = $this->_getConfig()->exclude;
		$exclude_string = "";
		for ($i = 0; $i < sizeof($exclude); $i++) 
			$exclude_string .= "- ".$exclude[$i] . "\n";
		$exclude_string .= "- deploy.php\n";
		$exclude_string .= "- **.dev.**\n";
		$exclude_file_name = _DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "exclude_file.txt";
		$handle = fopen($exclude_file_name, "w");
		fwrite($handle, $exclude_string);
		fclose($handle);
			
		// Setting up Rsync options
		if ($options['simulation'] === true) {
			// Simulation mode
			$option = 'rtOvn';
		} else {			
			// Live mode
			$option = 'rtOv';

			// Create a log entry for the pending deployement 
			$data = array (
				'DeploymentLog' => array (
					'project_id'	=> 	$project['Project']['id'],
					'title' 		=> 	date("D, M jS Y, H:i") . ' - ' . $project['Project']['name'],
					'user_id' 		=> 	$_SESSION['User']['id'],
					'comment' 		=> 	$options['comment'],
					'archive' 		=> 	0
				)
			);
			$this->DeploymentLog->save($data);
			
			// Backup
			if ($options['backup'] === true)
				if ( ($output .= $this->backup($project)) === false) {
					// Backup error
					$this->lastError = "Unable to backup";
					return false;
				}
		}

		// Preparing pathes
		$exclude_file_name = $this->_pathConverter($exclude_file_name);
		$source = $this->_pathConverter(_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS);
		$target = $this->_pathConverter($project['Project']['prd_path']);

		// Execute command		
		chdir(_DEPLOYDIR);
		$command = "rsync -$option --delete --exclude-from=$exclude_file_name $source $target 2>&1";	
		$output .= $this->executeCommand($command,'Deploying new version','synchronize');
		
		return $output;
	}// synchronize

	/**
	 * Step 4 of the deployment process: Finalize
	 * @param int $project 		Project that should be deployed 
	 * @param array $options	Various options used for configuring the step 
	 * @return string 			Shell output 
     */
	private function finalize($project = null, $options = array()) {
		if ( $project == null || !$this->isConfigAvailable($project['Project']['name'])) 
			return false;	
		
		set_time_limit(_TIMELIMIT_FINALIZE);
		
		include_once (_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS . "deploy.php");
			
		$default_options = array(
			'renamePrdFile' 	=> 	false,
			'changeFileMode' 	=> 	false,
			'giveWriteMode'		=> 	false
		);
		$options = array_merge($default_options, $options);
		$output = '';
		
		// Perform the job 
		chdir(_DEPLOYDIR);
		if (_WINOS === true) {
			//couche cygwin
			$prefix = "bash.exe --login -c '";
			$suffix = "'";
		} else {
			$prefix = "";
			$suffix = "";
		}
	
		if ($options['renamePrdFile'] === true) {
			// Rename file type from .prd.xxx into .xxx
			$command = $prefix."find " . $this->_pathConverter($project['Project']['prd_path']) . " -name '*.prd.*' -exec /usr/bin/perl ".$this->_pathConverter(_DEPLOYDIR)."/renamePrdFile -vf 's/\.prd\./\./i' {} \;".$suffix;
			$output .= $this->executeCommand($command, LANG_RENAMEFILES . '.prd.', 'finalize');
		}
		
		if ($options['changeFileMode'] === true) {
			$command = $prefix."find " . self :: pathConverter($project['Project']['prd_path']) . " -type f -exec chmod " . _FILEMODE . " {} \;".$suffix;
			$output .= $this->executeCommand($command, LANG_UPDATINGFILESMODES.' > '._FILEMODE, 'finalize');
			
			$command = "chmod " ._DIRMODE . "  ". self :: pathConverter($project['Project']['prd_path']);
			$output .= $this->executeCommand($command, LANG_UPDATINGDIRMODE.'1/2 > '._DIRMODE, 'finalize');

			$command = $prefix."find " . self :: pathConverter($project['Project']['prd_path']) . " -type d -exec chmod " . _DIRMODE . " {} \;".$suffix;
			$output .= $this->executeCommand($command, LANG_UPDATINGDIRMODE.'2/2 > '._DIRMODE, 'finalize');
		}
		
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
				$output .= "-[".LANG_CREATINGDIRECTORY. " " . _DEPLOYBACKUPDIR . "]\n";
		}

		// création du répertoire pour la sauvegarde
		if (!is_dir(_DEPLOYBACKUPDIR . DS . $project['Project']['name'])) {
			if (mkdir(_DEPLOYBACKUPDIR . DS . $project['Project']['name'], octdec(_DIRMODE), TRUE)) {
				$output .= "-[".LANG_CREATINGDIRECTORY. " " . _DEPLOYBACKUPDIR . DS . $project['Project']['name'] . "]\n";
			}
		}

		//
		$output .= "-[".LANG_BACKUPCURRENTPRODVERSION."]\n";
		if (is_dir($project['Project']['prd_path'])) {
			$source = $this->_pathConverter($project['Project']['prd_path'] );
			$target = $this->_pathConverter(_DEPLOYBACKUPDIR . DS . $project['Project']['name']);
		
			// rsync pour le backup
			$command = "rsync -av $source $target 2>&1";
			$output .= $this->executeCommand($command, LANG_BACKUPCURRENTPRODVERSION, 'backup');
			
			$command = "chmod -R " . _DIRMODE . " " . _DEPLOYBACKUPDIR;
			$output .= $this->executeCommand($command, LANG_UPDATINGDIRMODE . ' > ' . _DIRMODE, 'backup');
		} else {
			$output .= "-[".LANG_NOBACKUPNEEDED." " . $project['Project']['prd_path'] . " ".LANG_DOESNTEXIST."]\n";
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
	 * Convert if necessary a path to a cygwin/linux format 
	 * @param string $path 		Path to be converted
	 * @return string 			Converted path
	 */ 
	private function _pathConverter($path) {
		$pathForRsync = $path;
		if (_WINOS) {
			$pattern = '/^([A-Za-z]):/';
			preg_match($pattern, $path, $matches, PREG_OFFSET_CAPTURE);
			if (!empty ($matches[1][0])) {
				$windowsLetter = strtolower($matches[1][0]);
				$pathForRsync = strtr(_CYGWINROOT . $windowsLetter . substr($path, 2), "\\", "/");
			}	
		}
		return $pathForRsync;
	}// _pathConverter

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
	
	private function executeCommand( $command = null, $comment = 'none', $context = 'none' ){
		if ($command == null)
			return 'No command supplied';
			
		$output = "\n-[".$comment."]\n";
		if ( Configure::read() > 0 )
			CakeLog::write('debug', "[$context] " . $command);
		return $output. shell_exec($command);
	}// executeCommand
	
}// Deployment
?>