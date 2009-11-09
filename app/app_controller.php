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
	
	var $helpers = array ('Html', 'Form', 'Javascript', 'ContextElement');

	var $components = array ('Aclite', 'RequestHandler', 'Crumbs', 'ContextMenu');
				
	function beforeFilter() {
		// Loading lang
		$lang = $this->Session->read('User.Profile.lang');
		if (empty($lang)) {
			$lang = Configure::read('Fredistrano.language');
		}
		
		// Applying lang
		Configure::write('Config.language',$this->selectLanguage());
	}// beforeFilter
	
	function beforeRender() {
		// Disable cache due to a proxy issue
		$this->disableCache();
	}// beforeRender
	
	/**
	 * Return the language to use
	 * order config -> user profile -> selected during navigation
	 * @return $lang string
	 */
	function selectLanguage(){
		if ($this->Session->read('User.Profile.lang')) {
			return $this->Session->read('User.Profile.lang');
		}
		if (Configure::read('Language.default') !== null) {
			return Configure::read('Fredistrano.language');
		}
	}// selectLanguage
	
}// AppController
?>