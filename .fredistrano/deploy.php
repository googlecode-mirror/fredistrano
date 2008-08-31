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
 	
	// tableau permettant la création du fichier
	// des répertoires et fichiers à exclure lors du dépoiement en production
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
		
	// répertoires sur lesquel un CHMOD 777 sera executé à la suite du déploiement
	// les répertoires de log, de cache, les répertoires d'upload, etc ...
	var $writable = array (
		'app/tmp',
		'files/backup',
		'files/logs'
	);

 }// DEPLOY_CONFIG
?>