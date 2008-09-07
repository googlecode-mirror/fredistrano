<?php
// TODO Document code
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