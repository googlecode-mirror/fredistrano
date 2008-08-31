<?php
// TODO Document code
 class DEPLOY_CONFIG {

 	var $options = array(
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