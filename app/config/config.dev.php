<?php
//HTTPS authentification
define("_HTTPSENABLED",	0);							//enabled https 0 = never, 1 = login, 2 = always

//Comptes
define("_SVNUSER", "");								//default subversion login
define("_SVNPASS", "");								//default subversion password 

// Délais
define("_LOGSARCHIVEDATE",3 * 7 * 24 * 60 * 60);	//time before archiving logs 
define("_TIMELIMIT_EXPORT", 10 * 60); 				//max execution time for subversion export
define("_TIMELIMIT_RSYNC", 1 * 60);					//max execution time for rsync
define("_TIMELIMIT_FINALIZE", 5 * 60);				//max execution time after deploy
define("_TIMELIMIT_INITIALIZE", 5 * 60);			//max execution time before deploy

// Finalize options
define("_RENAMEPRDFILE", true);						//rename ".prd." file
define("_CHANGEMODE", true);						//change file and directory mode 
define("_GIVEWRITEMODE", true);						//give write access on directories define in deploy.php in $writable

// Permissions
define("_DIRMODE", '755');							//directories mode after deployment 
define("_FILEMODE", '644');							//files mode after deployment 
define("_WRITEMODE", '777');						//writable mode on directories defined in deploy.php in $writable after deployment 

// Filesytem
define("_FREDISTRANOPATH", dirname( dirname( dirname(__FILE__)))); 	//Fredistrano installation path  
define("_DEPLOYDIR", _FREDISTRANOPATH . DS . "files");				//required by Fredistrano for SVN export and backup
define("_DEPLOYTMPDIR", _DEPLOYDIR . DS . "tmp");					//required by Fredistrano for SVN export
define("_DEPLOYBACKUPDIR", _DEPLOYDIR . DS . "backup");				//required by Fredistrano for backup
define("_CYGWINROOT","/cygdrive/");									//only for cygwin windows server , example : "/cygdrive/" ou "/"

// Language
define('LANG_DEFAULT','en-US'); 					//available languages :  en-US, fr-FR
require_once (APP . 'locale' . DS . LANG_DEFAULT . DS . 'LC_MESSAGES' . DS . 'default.php');



//SPECIFIC - Will be removed in a futur version
define("_WEBSERVICESSERVER",	""); 				// endpoint URL
define("_AUTHENTICATIONTYPE",	"0");				// 0 = mysql / 1 = webservice + mysql
define("_DIRECTORYTYPE",		"20");				// 10 = genesis / 11 = yellowhat / 20 = dnsan
define("_WS_SSL_TRUSTEDCA_FILE","D:/Apache/Apache2/conf/ssl.crt/AdixenCA.crt");
?>
