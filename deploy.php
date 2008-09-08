<?php
/*
 * Created on 2 oct. 07
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 class DEPLOY_CONFIG {

	// tableau permettant la création du fichier
	// des répertoires et fichiers à exclure lors du dépoiement en production
	var $exclude = array (
		'/app/tmp/logs/*',
		'/app/tmp/sessions/*',
		'/app/tmp/tests/*',
		'/app/config/database.php',
		'/app/config/config.php',
		'/app/webroot/files/*',
		'/files/logs/*',
		'/files/tmp/*'
	);
		
	// répertoires sur lesquel un CHMOD 777 sera executé à la suite du déploiement
	// les répertoires de log, de cache, les répertoires d'upload, etc ...
	var $writable = array (
		'/app/tmp',
		'/files/backup',
		'/files/logs'
	);

 }

?>
