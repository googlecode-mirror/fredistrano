<?php
class HomeController extends AppController {

	var $uses = array (
		'Project',
		'DeploymentLog'
	);
	
	function beforeRender() {
		parent::beforeRender();
		
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array (
			'text' => 'See also'
		);

		$tab[] = array (
			'text' => __('About', true),
			'link' => '/pages/about'
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
		
	} // index
	
	/**
	 * Change current language
	 */
	function switchLanguage(){
		$_SESSION['userPreferedLanguage'] = $this->params['pass'][0];
		$this->redirect($this->referer());
	}// switchLanguage

} // HomeController
?>