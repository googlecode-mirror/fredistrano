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

	var $uses = array (
		'Project',
		'DeploymentLog'
	);
	
	function beforeRender() {
		parent::beforeRender();
		
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array (
			'text' => __('Navigation', true)
		);
		$tab[] = array (
			'text' => __('About', true),
			'link' => '/pages/about'
		);
		if ($this->Session->read('User')) {
			if ( Configure::read('Feeds.enabled') === true ) {
				$tab[] = array (
					'text' => __('Rss Feed', true),
					'link' => '/deploymentLogs/index.rss?token='.$this->Session->read('User.Profile.rss_token')
				);
			}
		}
		
		$tab[] = array (
			'text' => __('See also', true)
		);
		$tab[] = array (
			'text' => __('Homepage', true),
			'link' => 'http://code.google.com/p/fredistrano/'
		);
		$tab[] = array (
			'text' => __('Donate', true),
			'link' => 'http://code.google.com/p/fredistrano/wiki/Donation'
		);

		$this->set("context_menu", $tab);
	}

	/**
	 * Display the welcome screen
	 */
	function index() {
		if ($this->Session->read('User')) {
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

			$projects = $this->Project->find('list');
		
			$this->set('logs', $logs);
			$this->set('projects', $projects);
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