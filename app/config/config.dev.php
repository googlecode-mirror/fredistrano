<?php
//HTTPS authentification
define("_HTTPSENABLED",			false);				//enabled https

//Comptes
define("_SVNUSER", "");						//login subversion par défaut
define("_SVNPASS", "");					//password subversion par défaut

// Délais
define("_LOGSARCHIVEDATE",3 * 7 * 24 * 60 * 60);	//délai pour l'archive des logs 
define("_TIMELIMITSVN", 10 * 60); 					//durée maximum d'exécution d un export subversion
define("_TIMELIMITRSYNC", 5 * 60);					//durée maximum d'exécution d une synchro rsync

// Permissions
define("_DIRMODE", '750');							//permission sur les répertoires après le deploiement 755 en règle générale 
define("_FILEMODE", '640');							//permission sur les fichiers après le deploiement 644 en règle générale

// Filesytem
define("_FREDISTRANOPATH", dirname( dirname( dirname(__FILE__)))); 	//installation path de Fredistrano
define("_DEPLOYDIR", _FREDISTRANOPATH . DS . "files");				
define("_DEPLOYTMPDIR", _DEPLOYDIR . DS . "tmp");
define("_DEPLOYBACKUPDIR", _DEPLOYDIR . DS . "backup");
define("_CYGWINROOT","/cygdrive/");									//

//SPECIFIQUE -sera supprimé dans une prochaine version
define("_WEBSERVICESSERVER",	""); 	// endpoint URL
define("_AUTHENTICATIONTYPE",	"0");				// 0 = mysql / 1 = webservice + mysql
define("_DIRECTORYTYPE",		"20");				// 10 = genesis / 11 = yellowhat / 20 = dnsan
define("_WS_SSL_TRUSTEDCA_FILE","D:/Apache/Apache2/conf/ssl.crt/AdixenCA.crt");

?>
