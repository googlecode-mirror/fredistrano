<?php


/* SVN FILE: $Id: app_controller.php 4409 2007-02-02 13:20:59Z phpnut $ */
/**
 * Short description for file.
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2007, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2007, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision: 4409 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2007-02-02 07:20:59 -0600 (Fri, 02 Feb 2007) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * This is a placeholder class.
 * Create the same file in app/app_controller.php
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		cake
 * @subpackage	cake.cake
 */
class AppController extends Controller {
	var $helpers = array (
		'Html',
		'Form',
		'Error',
		'Pagination',
		'Ajax',
		'Javascript',
		'Table'
	);

	var $components = array (
		'Pagination',
		'RequestHandler',
		'Aclite',
		'Session'
	);
	
	var $authGlobal = array(
		'App' => array('administration'),
		'except' => array (
			'Home.index'=> array('public'),
			'Home.switchLanguage'=> array('public'),
			'Users.login'=> array('public'),
			'Users.logout'=> array('public')
		)
	);

	function beforeRender() {
		$this->set('referer', $this->referer());
		
		// pour construire l'url https du formulaire de login
		$this->set('serverName', $_SERVER['SERVER_NAME']);
		$this->set('appPath', str_replace('/app/webroot/index.php', '', $_SERVER['SCRIPT_NAME']));
	
		if (!$this->Session->check('user_windows')) {
			$output = array();
			exec('nbtstat -A ' . $_SERVER['REMOTE_ADDR'], $output);
			foreach($output as $value) {
				$matches = null;
				ereg('(.*)[[:space:]]+<03>[[:space:]]+UNIQUE[[:space:]]+Registered', $value, $matches);
				
				if (!empty($matches) && isset($matches[1])) {
						$tmp = trim($matches[1]);
						if (!$this->_isComputer($tmp)) {
							$this->Session->write('user_windows', strtolower($tmp));
							break;
						}
				} 
			}
		}
	}
	
//	function beforeFilter(){
//		// Default lang 
//		if (!defined('LANG_DEFAULT'))
//			define('LANG_DEFAULT','en-US');
//	
//		// Load the required lang file
//		$lang = isset ($_SESSION['userPreferedLanguage']) ? $_SESSION['userPreferedLanguage'] : LANG_DEFAULT;
//		require_once (APP . 'locale' . DS . $lang . DS . 'LC_MESSAGES' . DS . 'default.php');
//	}
	
	private function _isComputer($name) {
		if (($name[0] == '_')
		|| (substr($name, 0, 2) == 'XW')
		|| (substr($name, strlen($name) - 2) == 'PC'))
			return true;
		else
			return false;
	}
	


}
?>