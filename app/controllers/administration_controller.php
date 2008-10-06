<?php
/* SVN FILE: $Id$ */
/**
 * Controller for accessing administration ressources
 * 
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			app
 * @subpackage		app.controller
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Controller for accessing administration ressources
 *
 * @package		app
 * @subpackage	app.controller
 */
class AdministrationController extends AppController {
	
	var $uses = array ();

	var $authLocal = array (
		'Administration' => array (
			'administration'
		)
	);
	
	/**
	 * Default action
	 */
	function index() {
	
	}// index

} // Administration
?>