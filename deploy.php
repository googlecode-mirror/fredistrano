<?php
/*
 * Recette de déploiement
 *
 *
 *
 */

 class DEPLOY_CONFIG {

	// tableau permettant la création du fichier
	// des répertoires et fichiers à exclure lors du dépoiement en production
	var $exclude = array (
		'/app/tmp/cache/models/*',
		'/app/tmp/cache/persistent/*',
		'/app/tmp/cache/views/*',
		'/app/tmp/logs/*',
		'/app/tmp/sessions/*',
		'/app/tmp/tests/*',
		'/app/config/database.php',
		'/app/config/config.php',
		'.settings'
	);
	
	// répertoires sur lesquel un CHMOD 777 sera executé à la suite du déploiement
	// les répertoires de log, de cache, les répertoires d'upload, etc ...
	var $writable = array (
		'/app/tmp'
	);

 }
?>
