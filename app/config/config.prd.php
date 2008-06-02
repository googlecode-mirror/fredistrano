<?php
	
	// Version
	define("_VERSION",				'0.4.0');		
	define("_RELEASEDATE",			'XX/XX/2008');

	/**
	 * Enable Aclite (Authorization checks)
	 */
	Configure::write('Aclite.enabled',			false);
	
	/**
	 * Authentication type
	 *
	 * 0: disabled
	 * 1: web service
	 * 2: MySQL
	 */
	Configure::write('Authentication.type', 	2);
	
	/**
	 * Is cygwin required (ex. for using Fredistrano on Windows)
	 */
	Configure::write('OS.type',					strtoupper(substr(PHP_OS,0,3)));

	/**
	 * Cygwin root directory for drive
	 */
	Configure::write('OS.Cygwin.rootDir',		'/cygdrive/');
	
	/**
	 * Enable  public feeds
	 */
	Configure::write('Feeds.enabled',			true);
	
	/**
	 * Enabled https
	 *
	 * 0: never
	 * 1: login
	 * 2: always
	 */
	Configure::write('Security.https',			0);

	/**
	 * Subversion default login
	 *
	 * 0: never
	 * 1: login
	 * 2: always
	 */
	Configure::write('Subversion.user',			'');
	
	/**
	 * Subversion default password
	 *
	 * 0: never
	 * 1: login
	 * 2: always
	 */
	Configure::write('Subversion.passwd',		'');

	// Time limits
	define("_LOGSARCHIVEDATE",		3 * 7 * 24 * 60 * 60);		//time before archiving logs 
	define("_TIMELIMIT_EXPORT", 	10 * 60); 					//max execution time for subversion export
	define("_TIMELIMIT_RSYNC", 		1 * 60);					//max execution time for rsync
	define("_TIMELIMIT_FINALIZE", 	5 * 60);					//max execution time after deploy
	define("_TIMELIMIT_INITIALIZE", 5 * 60);					//max execution time before deploy

	// Default finalize options
	define("_RENAMEPRDFILE", 		true);						//rename ".prd." file
	define("_CHANGEMODE", 			true);						//change file and directory mode 
	define("_GIVEWRITEMODE", 		true);						//give write access on directories define in deploy.php in $writable

	// Default permissions
	define("_DIRMODE", 				'755');						//directories mode after deployment 
	define("_FILEMODE", 			'644');						//files mode after deployment 
	define("_WRITEMODE", 			'777');						//writable mode on directories defined in deploy.php in $writable after deployment 

	// Filesytem
	define("_FREDISTRANOPATH", 		dirname( dirname( dirname(__FILE__)))); 		//Fredistrano installation path  
	define("_DEPLOYDIR",			_FREDISTRANOPATH . DS . "files");				//required by Fredistrano for SVN export and backup
	define("_DEPLOYTMPDIR", 		_DEPLOYDIR . DS . "tmp");						//required by Fredistrano for SVN export
	define("_DEPLOYBACKUPDIR", 		_DEPLOYDIR . DS . "backup");					//required by Fredistrano for backup
	define("_DEPLOYLOGDIR", 		_DEPLOYDIR . DS . "logs");						//required by Fredistrano for deployment logs
	
?>