<?php
class HomeController extends AppController {

	var $uses = array (
		'Project',
		'DeploymentLog'
	);

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

		$projects = $this->Project->generateList();
		
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