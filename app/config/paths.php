<?php

	// Filesytem
	define('F_FREDISTRANOPATH', 	dirname( dirname( dirname(__FILE__))).DS); 		//Fredistrano installation path  
	define('F_DEPLOYDIR',			F_FREDISTRANOPATH.'files'.DS);				//required by Fredistrano for SVN export and backup
	define('F_DEPLOYTMPDIR', 		F_DEPLOYDIR.'tmp'.DS);						//required by Fredistrano for SVN export
	define('F_DEPLOYBACKUPDIR', 	F_DEPLOYDIR.'backup'.DS);					//required by Fredistrano for backup
	define('F_DEPLOYLOGDIR', 		F_DEPLOYDIR.'logs'.DS);						//required by Fredistrano for deployment logs

?>