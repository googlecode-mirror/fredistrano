<?php
/* SVN FILE: $Id$ */
/**
 * Database configuration file for the production environment
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
 * Database configuration file for the production environment 
 *
 * @package		app
 * @subpackage	app.config
 */
class DATABASE_CONFIG {
	var $default = array(
		'driver' => 'mysql',
		'persistent' => false,
		'host' => 'localhost',
		'port' => '',
		'login' => '',
		'password' => '',
		'database' => 'fredistrano',
		'schema' => '',
		'prefix' => '',
		'encoding' => 'utf8'
	);

	var $test = array (
			'driver' => 'mysql',
			'persistent' => false,
			'host' => 'localhost',
			'port' => '',
			'login' => 'root',
			'password' => 'root',
			'database' => 'qos_platform_test',
			'schema' => '',
			'prefix' => '',
			'encoding' => 'utf8'
		);
}
?>