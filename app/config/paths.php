<?php
/* SVN FILE: $Id$ */
/**
 * Path file
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
 * Path file
 *
 * @package		app
 * @subpackage	app.config
 */
	// Filesytem
	define('F_FREDISTRANOPATH', 	dirname( dirname( dirname(__FILE__))).DS); 		//Fredistrano installation path  
	define('F_DEPLOYDIR',			F_FREDISTRANOPATH.'files'.DS);				//required by Fredistrano for SVN export and backup
	define('F_DEPLOYTMPDIR', 		F_DEPLOYDIR.'tmp'.DS);						//required by Fredistrano for SVN export
	define('F_DEPLOYBACKUPDIR', 	F_DEPLOYDIR.'backup'.DS);					//required by Fredistrano for backup
	define('F_DEPLOYLOGDIR', 		F_DEPLOYDIR.'logs'.DS);						//required by Fredistrano for deployment logs

?>