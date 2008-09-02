<?php
	
	$config['Cygwin'] = array(
		'rootDir'		=> '/',
	);
	
	$config['Deployment'] = array(
		'options' 	=> array(
	 		'export' 		=> array(
	 		 	'runScript'				=> 	false	
	 		),
	 		'synchronize'	=> array(
	 			'backup'				=> 	false
	 		),
	 		'finalize'		=> array(
		 		'renamePrdFile' 		=> 	false,
				'changeFileMode' 		=> 	false,
				'giveWriteMode'			=> 	false,
				'modifiedFileOnly'		=> 	false,
	 			'runScript'				=> 	false
	 		)
	 	), 
		'timelimit' => 	array(
	 		'export' 		=> 10 * 60,
	 		'synchronize'	=> 1 * 60, 
			'finalize'		=> 1 * 60
		)
	); 
	
	$config['Feeds'] = array(
		'enabled'		=> true
	);
	
	$config['FileSystem'] = array(
		'permissions'	=> array(
			'files' 	=> '644',
			'writable'	=> '777'		// Bof
		)
	);
	
	$config['Log'] = array(
		'archiveDate'	=> 3 * 7 * 24 * 60 * 60,
		'maxSize'		=> 50000
	);
	
	$config['Security'] = array(
		'authenticationType'	=> 2,
		'authorizations'		=> true,
		'HTTPS'					=> 0
	);
	
	$config['Subversion'] = array(
		'user'			=> null,
		'passwd'		=> null
	);

	$config['Ui'] = array(
		'language'	=> 'en'
	);

?>