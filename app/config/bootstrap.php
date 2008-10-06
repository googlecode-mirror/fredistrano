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

	ini_set('zend.ze1_compatibility_mode', 'Off');
	
   	define('F_VERSION',			'1.0');		
   	define('F_RELEASEDATE',		'2008/XX/XX');
	define('F_OS',				strtoupper(substr(PHP_OS,0,3)));
	
   	require APP.'config'.DS.'paths.php';
   	Configure::load('config');
	
?>