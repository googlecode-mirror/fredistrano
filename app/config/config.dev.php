<?php
//HTTPS authentification
define("_HTTPSENABLED",			false);				//enabled https

//Facultatif utilisateur et mot de passe par défaut pour le serveur subversion
define("_SVNUSER", "");
define("_SVNPASS", "");

// Délai pour l'archive des logs 
define("_LOGSARCHIVEDATE",3 * 7 * 24 * 60 * 60);

//permission sur les répertoires après le deploiement 755 en règle générale 
define("_DIRMODE", 0755);

//permission sur les fichiers après le deploiement 644 en règle générale
define("_FILEMODE", 654);


// installation path de Fredistrano
define("_FREDISTRANOPATH",dirname( dirname( dirname(__FILE__))));


// Définition des dossiers utilisés par Fredistrano 
define("_DEPLOYDIR", _FREDISTRANOPATH . DS . "files");
define("_DEPLOYTMPDIR", _DEPLOYDIR . DS . "tmp");
define("_DEPLOYBACKUPDIR", _DEPLOYDIR . DS . "backup");

//durée maximum d'exécution 
define("_TIMELIMITSVN", 10 * 60);
define("_TIMELIMITRSYNC", 2 * 60);

//SPECIFIQUE -sera supprimé dans une prochaine version
define("_WEBSERVICESSERVER",	"hermes:50001"); 	// endpoint URL
define("_AUTHENTICATIONTYPE",	"0");				// 0 = mysql / 1 = webservice + mysql
define("_DIRECTORYTYPE",		"20");				// 10 = genesis / 11 = yellowhat / 20 = dnsan
define("_WS_SSL_TRUSTEDCA_FILE","D:/Apache/Apache2/conf/ssl.crt/AdixenCA.crt");

?>
