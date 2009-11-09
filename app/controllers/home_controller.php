<?php
/* SVN FILE: $Id$ */
/**
 * Controller that contains the welcome page
 * 
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			app
 * @subpackage		app.controllers
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Controller that contains the welcome page
 *
 * @package		app
 * @subpackage	app.controllers
 */
class HomeController extends AppController {

	var $uses = array('Project','DeploymentLog');
	
	var $helpers = array('Yui');
	
	function beforeRender() {
		parent::beforeRender();
				
		$this->ContextMenu->addSection(__('Navigation', true));
		$this->ContextMenu->addLink(__('About', true), '/pages/about');
		
		if ($this->Session->read('User')) {
			if ( Configure::read('Feeds.enabled') === true ) {
				$this->ContextMenu->addLink(__('Rss Feed', true),'/deploymentLogs/index.rss?token='.$this->Session->read('User.Profile.rss_token'));
			}
		}
		
		$this->ContextMenu->addSection(__('See also', true));
		$this->ContextMenu->addLink(__('Homepage', true), 'http://code.google.com/p/fredistrano/');
		$this->ContextMenu->addLink(__('Donate', true), 'http://code.google.com/p/fredistrano/wiki/Donation');
	}

	/**
	 * Display the welcome screen
	 */
	function index() {
		
		if ($this->Session->read('User')) {
			$projects = $this->Project->find('list', array('order'=>'Project.name ASC'));
			
			$fields = array (
				'id',
				'project_id',
				'user_id',
				'created',
				'comment',
				'User.login',
				'Project.name'
			);
			$order = 'DeploymentLog.created DESC';
			$logs = $this->DeploymentLog->findAll(null, $fields, $order,10);
			
			$this->set(compact('logs','projects'));
		}
	}// index
	
	/**
	 * Change current language
	 */
	function switchLanguage(){
		$_SESSION['userPreferedLanguage'] = $this->params['pass'][0];
		$this->redirect($this->referer());
	}// switchLanguage

} // HomeController
?>