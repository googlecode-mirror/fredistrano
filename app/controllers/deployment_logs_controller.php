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

	function beforeRender() {
		parent::beforeRender();
		$this->set('NYI', false);

		// Tableau de lien pour la création du menu contextuel
		$tab[] = array (
			'text' => 'Actions'
		);

		if ($this->action != 'list_all')
			$tab[] = array (
				'text' => 'Afficher tout l\'historique',
				'link' => '/deploymentLogs/list_all'
			);

		$tab[] = array (
			'text' => 'Lister les projets',
			'link' => '/projects'
		);

		// On passe le tableau de lien dans la variable links pour l'élément context_menu.thtml
		$this->set("context_menu", $tab);
	} // beforeRender

	// Available actions ----------------------------------------------------------------------------------
	function index() {
		$this->redirect('/deploymentLogs/list_all');
	} // index

	function list_all($op = null, $id = null) {
		$archived = $this->_archive();
		if ($archived > 0) 
			$this->Session->setFlash('Archivage automatique de '.$archived.' log(s)');
	
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
				$this->Session->setFlash('Opération non supportée (' . $op.')');
				$this->redirect('/deploymentLogs/list_all');
				break;
		} // switch
	} // listAll

	function view($id) {
		if (!$id or !$this->DeploymentLog->read(null, $id)) {
			$this->Session->setFlash('Identifiant de log inconnu ('.$id.')');
			$this->redirect('/deploymentLogs/list_all');
		}
		$this->set('log', $this->DeploymentLog->read(null, $id));
	} // view

	// Private functions ----------------------------------------------------------------------------------
	private function _archive() {
		$oldTime = time() - _LOGSARCHIVEDATE;
		return $this->DeploymentLog->archive($oldTime);
	}// _archive
	
	private function _reset() {
		$this->DeploymentLog->delAll();

		// Afichage
		$this->Session->setFlash('Tous les logs ont été supprimés');
		$this->redirect('/deploymentLogs/list_all');
	} //_ reset

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

	private function _listByProject($id = null) {
		if (!$id or !$this->Project->read(null, $id)) {
			$this->Session->setFlash('Identifiant de projet inconnu ('.$id.')');
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

	private function _listByPerson($id = null) {
		$this->set('filter', array (
			'person' => $id
		));
		$this->set('NYI', true);
	} // _listByPerson

} // DeploymentLogs
?>