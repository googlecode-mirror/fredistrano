<?php
/* SVN FILE: $Id$ */
/**
 * Configuration file for the development environment
 * 
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			app
 * @subpackage		app.config
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Configuration file for the development environment 
 *
 * @package		app
 * @subpackage	app.config
 */

	// only on windows server, root directory under cygwin
	$config['Cygwin'] = array(
		'rootDir'		=> '/cygdrive/',
	);
	
	// defaults options for all projects deployments
	// if necessary these options can be overloaded by a specific configuration in each project
	// or in the interface during a manual deployment
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
		'authenticationType'	=> 1,			//authentication type: 0 = accept all, 1 = custom, 2 = mysql 
		'authorizationsDisabled'=> false		//disable authorization
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