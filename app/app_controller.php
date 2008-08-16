<?php
/* SVN FILE: $Id: app_controller.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * Short description for file.
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
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
			'Javascript'
	);

	var $components = array (
		'Aclite',
		'RequestHandler'
	);
				
	function beforeFilter() {
		uses('L10n');
		$this->L10n = new L10n();
		$this->L10n->get('fr'); //get('en');
	}
			
	function beforeRender() {
		$this->set('referer', $this->referer());
		//disable cache due to a proxy issue
		$this->disableCache();
		
		// to construct https url for login form
		$this->set('serverName', $_SERVER['SERVER_NAME']);
		$this->set('appPath', str_replace('/app/webroot/index.php', '', $_SERVER['SCRIPT_NAME']));
	}		
	
}
?>