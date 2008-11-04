<?php
/* SVN FILE: $Id$ */
/**
 * Deployment configuration file for the current project (fredistrano)
 * 
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			files
 * @subpackage		files.templates.fredistrano
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Deployment configuration file for the current project (fredistrano)
 *
 * @package		files
 * @subpackage	files.templates.fredistrano
 */
class DEPLOY_CONFIG {
	
	// Default deployment options for the current project
	// these options could be modified during the deployment process in standard mode
	// in fast mode these options will be used
 	var $options = array(
 		'export' 		=> array(),
 		'synchronize'	=> array(
 		 	'runBeforeScript'		=> 	false, 		//enable custom script before deployement 
 			'backup'				=> 	false 		//enable backup functionality
 		),
 		'finalize'		=> array(
	 		'renamePrdFile' 		=> 	false,		//enable renaming .prd.xxx files
			'changeFileMode' 		=> 	false,		//enable updating file mode
			'giveWriteMode'			=> 	false,		//enable updating write mode on directories defined in $writable (in this file)
 			'runAfterScript'		=> 	false		//enable custom script at the end of the deployement process
 		)
 	);
 	
 	// path of yours custom scripts to execute at the beginning/end of the deployment process
	// if yours scripts are located in a directory named ".fredistrano" at the root of your project enter only the name of your script to execute
 	var $scripts = array(
 		'before' 	=>		'/path/to/file', 
 		'after' 	=>		'/path/to/file' 
 	);
 	
	// List of directories and files to exclude during the deployemnt process on the production server
	var $exclude = array (
		'app/tmp/logs/*',
		'app/tmp/sessions/*',
		'app/tmp/tests/*',
		'app/config/database.php',
		'app/config/config.php',
		'app/webroot/files/*',
		'files/logs/*',
		'files/tmp/*'
	);
		
	// Directories list on which the write permission will applied during the finalization step of the deployment process	
	// log, cache, upload directories, etc...
	var $writable = array (
		'app/tmp',
		'files/backup',
		'files/logs'
	);

 }// DEPLOY_CONFIG
?>