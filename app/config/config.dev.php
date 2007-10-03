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

// ne pas modifier, installation path de Fredistrano
define("_FREDISTRANOPATH",dirname( dirname( dirname(__FILE__))));

// Définition des dossiers utilisés par Fredistrano 
define("_DEPLOYDIR", _FREDISTRANOPATH . DS . "files");
define("_DEPLOYTMPDIR", _DEPLOYDIR . DS . "tmp");
define("_DEPLOYBACKUPDIR", _DEPLOYDIR . DS . "backup");

//Facultatif utilisateur et mot de passe par défaut pour le serveur subversion
define("_SVNUSER", "");
define("_SVNPASS", "");


// Délai pour l'archive des logs 
define("_LOGSARCHIVEDATE",3 * 7 * 24 * 60 * 60);


//Droits sur les dossiers et fichiers lors d'un déploiement
define("_DIRMODE", 0755);

define("_FILEMODE", 654);
?>
