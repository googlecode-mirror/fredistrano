<?php

//serveur pour les web services
define("_WEBSERVICESSERVER","hermes:50000");

/*
 * méthode d'authentification
 * 1 = webservice + mysql
 * 0 = mysql
 *
 */
define("_AUTHENTICATIONTYPE", "0");

/*
 * annuaire utilisé par le webservice pour le webservice
 * 10 genesis
 * 11 yellowhat
 * 20 dnsan
*/
define("_DIRECTORYTYPE", "20");

//chemin de la racine du serveur web
define("_PRDROOT","/home/user/deploy.fbollon.net");

//dossier de déploiement
define("_PRDDEPLOYDIR","_deployment");

//dossier temporaire utilisé pour le svn export
define("_PRDTMPDIR", _PRDDEPLOYDIR."/tmp");

//dossier de backup pour sauvegarder les applications avant le rsync
define("_PRDBACKUP", _PRDDEPLOYDIR."/backup");

// login et password par défaut pour subversion
define("_SVNUSER", "");
define("_SVNPASS", "");

// Archive logs 
define("_LOGSARCHIVEDATE",3 * 7 * 24 * 60 * 60);
?>
