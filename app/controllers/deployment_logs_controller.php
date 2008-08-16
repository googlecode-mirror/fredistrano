<?php
class DeploymentLogsController extends AppController {

	var $name = 'DeploymentLogs';

	var $helpers = array (
		'Html',
		'Text'
	);

	var $uses = array (
		'Project',
		'DeploymentLog'
	);

	var $authLocal = array (
		'DeploymentLogs'	=> 	array( 'entrance' )
	);
		
	function beforeRender() {
		parent::beforeRender();

		// Tableau de lien pour la création du menu contextuel
		$tab[] = array (
			'text' => 'Actions'
		);

		if ($this->action != 'list_all')
			$tab[] = array (
				'text' => __('Display full history', true),
				'link' => '/deploymentLogs/list_all'
			);

		$tab[] = array (
			'text' => __('List projects', true),
			'link' => '/projects'
		);
		
		if ( Configure::read('Feeds.enabled') === true ) {
			$tab[] = array (
				'text' => 'Rss Feed',
				'link' => '/rss/deploymentLogs'
			);
		}
		
		// On passe le tableau de lien dans la variable links pour l'élément context_menu.thtml
		$this->set("context_menu", $tab);
	} // beforeRender

	// Available actions ----------------------------------------------------------------------------------
	/**
	 * List all logs
	 */
	function index($op = null, $id = null) {
		if (isset($this->data['Log']['project_id'])) {
			$op = 'project';
			$id = $this->data['Log']['project_id'];
		}
		
		$this->set('projects', $this->Project->find('list'));
		$archived = $this->_archive();
		if ($archived > 0){ 
			$this->Session->setFlash(sprintf(__("%d logs have been archived", true),$archived));
			}

		switch ($op) {
			case null :
				$this->_listAll();
				break;
			case 'person' :
				$this->_listByPerson($id);
				break;
			case 'project' :
				$this->_listByProject($id);
				break;
			default :
				$this->Session->setFlash(__('Unsupported action ', true) . $op);
				$this->redirect('/deploymentLogs/list_all');
				break;
		} // switch
	} // listAll

	/**
	 * View properties of a specified log
	 * @param string $id ID of the log to be viewed
	 */
	function view($id) {
		if (!$id or !($deployLog = $this->DeploymentLog->read(null, $id)) ) {
			$this->Session->setFlash(__('Invalid id', true));
			$this->redirect('/deploymentLogs/list_all');
		}
		
		$options = array(
			'reverse'			=>	false,
			'logPath'			=> _DEPLOYLOGDIR.DS.$deployLog['DeploymentLog']['uuid'].'.log'
		);
		$output = $this->Project->readAssociatedLog($deployLog['DeploymentLog']['project_id'], $options);
		if ( $output === false ) {
			$this->set('error', 	$this->Project->lastReadError);
		} else {
			$this->set('project', 	$this->Project->read(null, $this->data['Search']['project_id']));
			$this->set('size', 		$this->Project->lastReadSize);
			$this->set('logPath',	$options['logPath']); 
		}
		$this->set('log',	 	$output);
		$this->set('deployLog', $deployLog);
		$this->set('project', 	$this->Project->read(null, $this->data['Search']['project_id']));		
	} // view

	// Private functions ----------------------------------------------------------------------------------
	/**
	 * Private function for archiving old logs
	 */
	private function _archive() {
		$oldTime = time() - _LOGSARCHIVEDATE;
		return $this->DeploymentLog->archive($oldTime);
	}// _archive

	/**
	 * Private function for deleting of all logs
	 */
	private function _reset() {
		$this->DeploymentLog->delAll();

		// Afichage
		$this->Session->setFlash(__('All logs deleted', true));
		$this->redirect('/deploymentLogs/list_all');
	} //_ reset

	/**
	 * Private function for listing logs
	 */
	private function _listAll() {
		$filter = array();
		$conditions = '';
		if (!isset($this->params['url']['showArchived'])) 
			$conditions = 'archive=0';
		
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
		$logs = $this->DeploymentLog->findAll($conditions, $fields, $order);

		$this->set('filter', $filter);
		$this->set('logs', $logs);
	} // _listAll

	/**
	 * Private function for listing logs associated to a project
	 * @param string $id ID of the project
	 */
	private function _listByProject($id = null) {
		
		if ($id && !$this->Project->read(null, $id)) {
			$this->Session->setFlash(__('Invalid id', true));
		}
		
		$conditions = '1=1';
		if ($id) {
			$conditions .= ' AND DeploymentLog.project_id = ' . $id;
		}
		if (!isset($this->params['url']['showArchived']) && (!isset($this->data['Log']['showArchived']) || $this->data['Log']['showArchived'] == 0)){
			$conditions .= ' AND archive=0';
		}	
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
		$logs = $this->DeploymentLog->findAll($conditions, $fields, $order);

		$this->set('logs', $logs);
	} // _listByProject

} // DeploymentLogs
?>