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
 * @subpackage		files.templates
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Deployment configuration file for the current project (fredistrano)
 *
 * @package		files
 * @subpackage	files.templates
 */
 class DEPLOY_CONFIG {

 	var $options = array(
 		'export' 		=> array(),
 		'synchronize'	=> array(
	 		'runBeforeScript'		=> 	true,
 			'backup'				=> 	true
 		),
 		'finalize'		=> array(
	 		'renamePrdFile' 		=> 	true,
			'changeFileMode' 		=> 	true,
			'giveWriteMode'			=> 	true,
 			'runAfterScript'		=> 	true
 		)
 	);

	// Scripts à exécuter avant/après que les fichiers ne soient modifiés
	var $scripts = array(
		'before' 	=>		'/path/to/file',
		'after' 	=>		'/path/to/file'
	);
 	
	var $exclude = array (
		'.fredistrano',
		'app/tmp/logs/*',
		'app/tmp/sessions/*',
		'app/tmp/tests/*',
		'app/config/database.php',
		'app/config/config.php',
		'app/webroot/files/*',
		'files/logs/*',
		'files/tmp/*'
	);
		
	var $writable = array (
		'app/tmp',
		'files/backup',
		'files/logs'
	);

 }// DEPLOY_CONFIG
?>