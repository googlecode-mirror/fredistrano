<?php
/* SVN FILE: $Id$ */
/**
 * Example of custom authentication 
 * the function authenticate may be modified according to your needs
 * 
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			app
 * @subpackage		app.vendors
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Provides classes for implementing an advanced logging system
 *
 * @package		app
 * @subpackage	app.vendors
 */
/**
 * Example of custom authentication 
 * the function authenticate may be modified according to your needs
 */
class CustomAuthentication {

	/**
	 * Check if the given user has valid credentials
	 * 
	 * @param string $user username as submitted by the form
	 * @param string $passwd password as submitted by the form
	 * @return  boolean true if authenticated; false otherwise
	 */
	public static function authenticate($user, $passwd){
		
		// Write your custom code here
		
		return false;
	}// authenticate

}// CustomAuthentication
?>