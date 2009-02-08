<?php
/* SVN FILE: $Id$ */
/**
 * Parent controller in fredsitrano
 * 
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			app
 * @subpackage		app
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Parent controller in fredsitrano
 *
 * @package		app
 * @subpackage	app
 */
class AppController extends Controller {
	
	var $helpers = array ('Html','Form','Javascript');

	var $components = array ('Aclite','RequestHandler');
				
	function beforeFilter() {
		// Loading lang
		$lang = $this->Session->read('User.Profile.lang');
		if (empty($lang)) {
			$lang = Configure::read('Fredistrano.language');
		}
		
		// Applying lang
		uses('L10n');
		$this->L10n = new L10n();
		$this->L10n->get($lang);
		
		// Disable cache due to a proxy issue
		$this->disableCache();
	}// beforeFilter
	
}// AppController
?>