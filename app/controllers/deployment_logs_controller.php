<?php
class DeploymentLogsController extends AppController {

	var $name = 'DeploymentLogs';

	var $helpers = array (
		'Html'
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
				'text' => LANG_DISPLAYFULLHISTORY,
				'link' => '/deploymentLogs/list_all'
			);

		$tab[] = array (
			'text' => LANG_PROJECTLIST,
			'link' => '/projects'
		);
		
		$tab[] = array (
			'text' => 'Rss Feed',
			'link' => '/rss/deploymentLogs'
		);

		// On passe le tableau de lien dans la variable links pour l'élément context_menu.thtml
		$this->set("context_menu", $tab);
	} // beforeRender

	// Available actions ----------------------------------------------------------------------------------
	/**
	 * List all logs
	 */
	function index() {
		$this->redirect('/deploymentLogs/list_all');
	} // index

	/**
	 * List all logs
	 */
	function list_all($op = null, $id = null) {
		$archived = $this->_archive();
		if ($archived > 0) 
			$this->Session->setFlash(sprintf(LANG_AUTOLOGARCHIVE,$archived));

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
				$this->Session->setFlash(LANG_UNSUPPORTEDACTION . $op);
				$this->redirect('/deploymentLogs/list_all');
				break;
		} // switch
	} // listAll

	/**
	 * View properties of a specified log
	 * @param string $id ID of the log to be viewed
	 */
	function view($id) {
		if (!$id or !$this->DeploymentLog->read(null, $id)) {
			$this->Session->setFlash(LANG_INVALIDLOGID.$id);
			$this->redirect('/deploymentLogs/list_all');
		}
		$this->set('log', $this->DeploymentLog->read(null, $id));
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
		$this->Session->setFlash(LANG_ALLLOGSDELETED);
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
		if (!$id or !$this->Project->read(null, $id)) {
			$this->Session->setFlash(LANG_INVALIDPROJECTID.$id);
			$this->redirect('/deploymentLogs/list_all');
		}
		
		$filter = array('project' => $id);		
		$conditions = 'DeploymentLog.project_id = ' . $id;
		if (!isset($this->params['url']['showArchived'])){
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
		$this->set('filter',$filter);
	} // _listByProject

} // DeploymentLogs
?>