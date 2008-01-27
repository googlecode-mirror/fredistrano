<?php
class ProjectsController extends AppController {

	var $name = 'Projects';
	
	var $helpers = array (
		'Html',
		'Form',
		'Ajax',
		'Pagination',
		'Error',
		'Adixen'
	);
	
	var $components = array (
		'Pagination'
	);

	var $uses = array (
		'Project'
	);

	var $authLocal = array (
		'Projects'	=> 	array( 'entrance' ), 
		'except' 	=> 	array(
			'edit' 		=> 	array( 'buinessData' ),
			'add'		=> 	array( 'buinessData' ),
			'delete' 	=> 	array( 'buinessData' ),
			'deploy' 	=> 	array( 'buinessData' )
		)
	);
	
	function beforeRender() {
		parent::beforeRender();
		
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array (
			'text' => 'Actions'
		);

		if ($this->action != 'index')
			$tab[] = array (
				'text' => LANG_PROJECTSLIST,
				'link' => '/projects/index'
			);

		if ($this->action != 'add')
			$tab[] = array (
				'text' => LANG_ADDPROJECT,
				'link' => '/projects/add'
			);

		$tab[] = array (
			'text' => LANG_DISPLAYFULLHISTORY,
			'link' => '/deploymentLogs'
		);
		$this->set("context_menu", $tab);
	}

	// Public actions -----------------------------------------------------------
	/**
	 * List all projects
	 */
	function index() {
		$criteria = NULL;
		$this->Pagination->sortBy = 'name';
		list ($order, $limit, $page) = $this->Pagination->init($criteria); // Added
		$data = $this->Project->findAll($criteria, NULL, $order, $limit, $page); // Extra parameters added
		$this->set('data', $data);
	}// index

	/**
	 * Deploy a project
	 * @param string $id ID of the project to be deployed
	 */
	function deploy($id = null) {
		$this->layout = 'ajax';
		$this->set('id', $id);
	}// deploy

	/**
	 * View properties of a specified project
	 * @param string $id ID of the project to be viewed
	 */
	function view($id = null) {
		if (!$id) {
			$this->Session->setFlash(LANG_INVALIDID);
			$this->redirect('/projects/index');
		}
		$project = $this->Project->read(null, $id);
		$this->set('project', $project);
	}// view

	/**
	 * Add a new project
	 */
	function add() {
		if (empty ($this->data)) {
			$this->render();
		} else {
			$this->cleanUpFields();
			if ($this->Project->save($this->data)) {
				$this->Session->setFlash(LANG_PROJECTSAVED);
				$this->redirect('/projects/index');
			} else {
				$this->Session->setFlash(LANG_CORRECTERRORSBELOW);
			}
		}
	}// add 
	
	/**
	 * Edit a project properties
	 * @param string $id ID of the project to be edited 
	 */
	function edit($id = null) {
		if (empty ($this->data)) {
			if (!$id) {
				$this->Session->setFlash(LANG_INVALIDID);
				$this->redirect('/projects/index');
			}
			$this->data = $this->Project->read(null, $id);
		} else {
			$this->cleanUpFields();
			if ($this->Project->save($this->data)) {
				$this->Session->setFlash(LANG_PROJECTSAVED);
				$this->redirect('/projects/view/' . $id);
			} else {
				$this->Session->setFlash(LANG_CORRECTERRORSBELOW);
			}
		}
	}// edit
	
	/**
	 * Delete a project
	 * @param string $id ID of the project to be deleted
	 */
	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(LANG_INVALIDID);
			$this->redirect('/projects/index');
		}
		if ($this->Project->del($id)) {
			$this->Session->setFlash(LANG_PROJECTDELETED);
			$this->redirect('/projects/index');
		}
	}// delete
	
}// ProjectsController
?>