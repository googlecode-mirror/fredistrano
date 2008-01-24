<?php
class Deployment extends AppModel {

	var $name = 'Deployment';

	var $Project = null;
	
	var $DeploymentLog = null;
	
	function __construct() {
		parent :: __construct();
		
		loadModel('Project');
		$this->Project = new Project(); 
		
		loadModel('DeploymentLog');
		$this->DeploymentLog = new DeploymentLog();
	}// __construct 
	
	// Public deploy -----------------------------------------------------------------		
	function execute($project_id = null, $options = array()) {

		if ( $project_id == null || !($project = $this->Project->read(null, $project_id))
				|| !$this->isConfigAvailable($project['Project']['name'])) 
			return false;		
	
		// Init options
		if (empty($options))
			$options = array(
				'backup'		=>	true,
				'simulation' 	=> 	true,
				'comment' 		=> 	'none'
				'renamePrdFile' 	=> 	false,
				'changeFileMode' 	=> 	false,
				'giveWriteMode'		=> 	false
			);

		// Performs successively each step
		$output = "> Initialize:\n";
		$output .= $this->initialize($project_id, $options);
		
		$output = "> Export:\n";
		$output .= $this->export($project_id, $options);
		
		$output = "> Synchronize:\n";
		$output .= $this->synchronize($project_id, $options);

		$output = "> Finalize:\n";
		$output .= $this->finalize($project_id, $options);
		
		return $output;
	}// execute
			
	/**
	 * Step 1 of the deployment process: Initialize
	 * @param int $project_id 	Id of the project that should be deployed 
	 * @param array $options	Various options used for configuring the step 
	 * @return string 			Shell output 
     */
	function initialize($project_id = null, $options = array()) {

		if ( $project_id == null || !($project = $this->Project->read(null, $project_id))
				|| !$this->isConfigAvailable($project['Project']['name'])) 
			return false;		

		include_once (_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS . "deploy.php");
			
		$default_options = array(
			'XXX' 	=> 	false
		);
		$options = array_merge($default_options, $options);
		$output = '';

		// Perform the job 
		
		return $output;
	}// initialize
	
	/**
	 * Step 2 of the deployment process: Export
	 * @param int $project_id 	Id of the project that should be deployed 
	 * @param array $options	Various options used for configuring the step 
	 * @return string 			Shell output 
     */
	
	function export($project_id = null, $options = array()) {
	
		if ( $project_id == null || !($project = $this->Project->read(null, $project_id))
				|| !$this->isConfigAvailable($project['Project']['name'])) 
			return false;		

		include_once (_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS . "deploy.php");
			
		$default_options = array(
			'XXX' 	=> 	false
		);
		$options = array_merge($default_options, $options);
		$output = '';
		
		// Perform the job 
				
		return $output;
	}// export
	
	/**
	 * Step 3 of the deployment process: Synchronize
	 * Synchronize the exported source code from snv with the code located in the target directory
	 * @param int $project_id 	Id of the project that should be deployed 
	 * @param array $options	Various options used for configuring the step 
	 * @return string 			Shell output 
     */
	function synchronize($project_id =null, $options = array()) {
		
		if ( $project_id == null || !($project = $this->Project->read(null, $project_id))
				|| !$this->isConfigAvailable($project['Project']['name'])) 
			return false;		

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

		// Backup current code
		if ($options['backup'])
			if (!$this->_backup($project, $output))
				// Backup error
				return false;
			
		// Setting up Rsync options
		if ($options['simulation'] == 1) {
			// Simulation mode
			$option = 'rtOvn';
		} else {
			// Live mode
			$option = 'rtOv';

			// Create a log entry for the pending deployement 
			$data = array (
				'DeploymentLog' => array (
					'project_id'	=> 	$this->data['Project']['id'],
					'title' 		=> 	$project['Project']['name'] . ' - ' . $_SESSION['User']['login'],
					'user_id' 		=> 	$_SESSION['User']['id'],
					'comment' 		=> 	$options['comment'],
					'archive' 		=> 	0
				)
			);
			$this->DeploymentLog->save($data);
		}

		// Preparing pathes
		$exclude_file_name = $this->_pathConverter($exclude_file_name);
		$source = $this->_pathConverter(_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS);
		$target = $this->_pathConverter($project['Project']['prd_path']);

		// Execute command
		chdir(_DEPLOYDIR);
		$output .= shell_exec("rsync -$option --delete --exclude-from=$exclude_file_name $source $target");
		
		return $output;
	}// synchronize

	/**
	 * Step 4 of the deployment process: Finalize
	 * @param int $project_id 	Id of the project that should be deployed 
	 * @param array $options	Various options used for configuring the step 
	 * @return string 			Shell output 
     */
	function finalize($project_id = null, $options = array()) {
		if ( $project_id == null || !($project = $this->Project->read(null, $project_id))
				|| !$this->isConfigAvailable($project['Project']['name'])) 
			return false;		

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
			$output .= "\n-[".LANG_RENAMEFILES." '.prd.']\n";
			$output .= shell_exec($prefix."find " . $this->_pathConverter($project['Project']['prd_path']) . " -name '*.prd.*' -exec /usr/bin/perl ".$this->_pathConverter(_DEPLOYDIR)."/renamePrdFile -vf 's/\.prd\./\./i' {} \;".$suffix);				
		}
		
		if ($options['changeFileMode'] === true) {
			// Adjust file mode
			$output .= "\n-[".LANG_UPDATINGFILESMODES."] ".LANG_NEWFILESMODE.": " . _FILEMODE;
			$output .= shell_exec("chmod -R " ._FILEMODE . "  ".$this->_pathConverter($project['Project']['prd_path']));	
			$output .= "\n-[".LANG_UPDATINGDIRMODE." ] ".LANG_NEWDIRMODES.": " . _DIRMODE;
			$output .= shell_exec("chmod " ._DIRMODE . "  ".$this->_pathConverter($project['Project']['prd_path']));
			$output .= shell_exec($prefix."find " . $this->_pathConverter($project['Project']['prd_path']) . " -type d -exec chmod " . _DIRMODE . " {} \;".$suffix);
		}
		
		if ($options['giveWriteMode'] === true) {
			// Give write permissions to some folder
			$output .= "\n-[".LANG_ADDWRITABLEMODE."]\n";
			$writable = $this->_getConfig()->writable;
			if (sizeof($writable) > 0) {
				for ($i = 0; $i < sizeof($writable); $i++) {
					$output .= shell_exec("chmod -vR " ._WRITEMODE . "  ".$this->_pathConverter($project['Project']['prd_path'] . $writable[$i] ));
				}
			}
		}
		
		return $output;
	}// finalize
		
	//  Public all-----------------------------------------------------------
	/**
	 * Optional step in the deployment process: Backup
	 * @param int $project_id 	Id of the project that should be deployed 
	 * @param array $options	Various options used for configuring the step 
	 * @return string 			Shell output 
     */
	function backup($project, $output) {

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
			// rsync pour le backup
			$output .= shell_exec("rsync -av " . $project['Project']['prd_path'] . " " . _DEPLOYBACKUPDIR . DS);
			$output .= shell_exec("chmod -R " . _DIRMODE . " " . _DEPLOYBACKUPDIR);

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
		return @ file_exists(_DEPLOYTMPDIR . DS . $projectName . DS . "tmpDir" . DS . "deploy.php");
	}// isConfigAvailable
	
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
	
}// ControlObject
?>