# Introduction #

The deployment options can be set at tree different level in this order :
  1. in the file fredistrano/app/config/config.php (common options for all the deployments)
  1. in the file .fredistrano/deploy.php in the project to deploy (specific for each project)
  1. with the interface during the mannually deployement process

# Global config  - config.php #

```
<?php
	// only on windows server, root directory under cygwin
	$config['Cygwin'] = array(
		'rootDir'		=> '/cygdrive/',
	);
	
	/** 
          * Defaults options for all projects deployments
	  * if necessary these options can be overloaded by a specific configuration in each project
          * or in the interface during a manual deployment
	  */
        $config['Deployment'] = array(
		'options' 	=> array(
	 		'export' 		=> array(),
	 		'synchronize'	=> array(
	 			'backup'			=> 	false, 	//true to activate the backup fonctionality
	 		 	'runBeforeScript'	=> 	false 	//true to activate the execution of your own customs scripts before the deployment 
	 		),
	 		'finalize'		=> array(
		 		'renamePrdFile' 	=> 	false,	//true to activate the renaming of ".prd." files
				'changeFileMode'	=> 	false,	//true to activate the updating of file and directories mode
				'giveWriteMode'		=> 	false,	//true to give write access on directories define in deploy.php 
	 			'runAfterScript'	=> 	false	//true to activate the execution of your own customs scripts after the deployment 
	 		)
	 	), 
		'timelimit' => 	array(
	 		'export' 		=> 10 * 60,			//max execution time for subversion export
	 		'synchronize'	=> 1 * 60, 			//max execution time for rsync
			'finalize'		=> 1 * 60			//max execution time after deploy
		)
	); 
	
	$config['Feeds'] = array(
		'enabled'		=> true					//enable RSS Feeds of deployment history 
	);
	
	$config['FileSystem'] = array(
		'permissions'	=> array(
			'files' 	=> '644',				//files mode after deployment, the directories mode will be calculate on this basis
			'writable'	=> '777'				//writable mode on directories defined in deploy.php
		)
	);
	
	$config['Log'] = array(
		'archiveDate'	=> 3 * 7 * 24 * 60 * 60,//time before archiving logs
		'maxSize'		=> 50000				//maximum size for the reading of the logs files 
	);
	
	$config['Security'] = array(
		'authenticationType'	=> 2,			//authentication type: 0 = accept all, 1 = custom, 2 = mysql 
		'authorizationsDisabled'=> false,		//disable authorization
		'HTTPS'					=> 0			//enabled https: 0 = never, 1 = login, 2 = always
	);
	
	$config['Subversion'] = array(
		'user'			=> null,				//default subversion login
		'passwd'		=> null,				//default subversion password 
		'configDirectory'	=> null,			//default subversion configuration directory
		'parseResponse'	=> true,				//activate the command response parsing
	);

	$config['Fredistrano'] = array(
		'language'	=> 'en'						//default language 
	);
?>
```

# Project config : deploy.php #

```
<?php
 class DEPLOY_CONFIG {
	
	// Default deployment options for the current project
	// these options could be modified during the deployment process in standard mode
	// in fast mode these options will be used
 	var $options = array(
 		'export' 		=> array(),
 		'synchronize'	=> array(
 		 	'runBeforeScript'		=> 	true, 		//enable custom script before deployement 
 			'backup'				=> 	false 		//enable backup functionality
 		),
 		'finalize'		=> array(
	 		'renamePrdFile' 		=> 	true,		//enable renaming .prd.xxx files
			'changeFileMode' 		=> 	true,		//enable updating file mode
			'giveWriteMode'			=> 	true,		//enable updating write mode on directories defined in $writable (in this file)
 			'runAfterScript'		=> 	true		//enable custom script at the end of the deployement process
 		)
 	);
 	
 	// path of yours custom scripts to execute at the beginning/end of the deployment process
	// if yours scripts are located in a directory named ".fredistrano" at the root of your project enter only the name of your script to execute
 	var $scripts = array(
 		'before' 	=>		'beforeScript', 
 		'after' 	=>		'afterScript' 
 	);
 	
	// List of directories and files to exclude during the deployemnt process on the production server
	var $exclude = array (
		'/app/tmp/logs/*',
		'/app/tmp/sessions/*',
		'/app/tmp/tests/*',
		'/app/config/database.php',
		'/app/config/config.php',
		'/app/webroot/files/*',
		'/.settings',
		'/.htaccess',
		'/.fredistrano',
		'/302.*'
	);
		
	// Directories list on which the write permission will applied during the finalization step of the deployment process	
	// log, cache, upload directories, etc...
	var $writable = array (
		'app/tmp',
		'app/webroot/files'
	);

 }// DEPLOY_CONFIG
?>
```