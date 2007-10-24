<?php
//HTTPS authentification
define("_HTTPSENABLED",	0);							//enabled https 0 = never, 1 = login, 2 = always

//Comptes
define("_SVNUSER", "");								//login subversion par défaut
define("_SVNPASS", "");								//password subversion par défaut

// Délais
define("_LOGSARCHIVEDATE",3 * 7 * 24 * 60 * 60);	//délai pour l'archive des logs 
define("_TIMELIMIT_EXPORT", 10 * 60); 				//durée maximum d'exécution d un export subversion
define("_TIMELIMIT_RSYNC", 1 * 60);					//durée maximum d'exécution d une synchro rsync
define("_TIMELIMIT_FINALIZE", 5 * 60);				//durée maximum d'exécution apres le deploy
define("_TIMELIMIT_INITIALIZE", 5 * 60);			//durée maximum d'exécution avant le deploy

// Finalize options
define("_RENAMEPRDFILE", true);						//rename ".prd." file
define("_CHANGEMODE", true);						//change file and directory mode 
define("_GIVEWRITEMODE", true);						//give write access on directories define in deploy.php in $writable

// Permissions
define("_DIRMODE", '750');							//permission sur les répertoires après le deploiement 750 en règle générale 
define("_FILEMODE", '640');							//permission sur les fichiers après le deploiement 640 en règle générale
define("_WRITEMODE", '777');						//droits d'écriture sur les dossiers définit dans deploy.php dans $writable après le deploiement 777 en règle générale

// Filesytem
define("_FREDISTRANOPATH", dirname( dirname( dirname(__FILE__)))); 	//installation path de Fredistrano
define("_DEPLOYDIR", _FREDISTRANOPATH . DS . "files");				//nécessaire à Fredistrano pour stocker les exports SVN et les backups
define("_DEPLOYTMPDIR", _DEPLOYDIR . DS . "tmp");					//nécessaire à Fredistrano pour stocker les exports SVN
define("_DEPLOYBACKUPDIR", _DEPLOYDIR . DS . "backup");				//nécessaire à Fredistrano pour stocker les backups
define("_CYGWINROOT","/cygdrive/");									//uniquement pour cygwin sur les serveurs windows, en générale "/cygdrive/" ou "/"

//SPECIFIQUE -sera supprimé dans une prochaine version
define("_WEBSERVICESSERVER",	""); 				// endpoint URL
define("_AUTHENTICATIONTYPE",	"0");				// 0 = mysql / 1 = webservice + mysql
define("_DIRECTORYTYPE",		"20");				// 10 = genesis / 11 = yellowhat / 20 = dnsan
define("_WS_SSL_TRUSTEDCA_FILE","D:/Apache/Apache2/conf/ssl.crt/AdixenCA.crt");
?>
