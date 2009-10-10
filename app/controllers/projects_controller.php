<?php
/* SVN FILE: $Id$ */
/**
 * Controller that manages the master data of fredistrano
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
 * Controller that manages the master data of fredistrano
 *
 * @package		app
 * @subpackage	app.controllers
 */
class ProjectsController extends AppController {

	var $name = 'Projects';
	
	var $helpers = array ('Ajax','Fbollon');

    var $paginate = array('limit' => 15, 'page' => 1, 'order' => 'name'); 

	var $uses = array ('Project');

	var $authLocal = array (
		'Projects'	=> 	array( 'entrance' ), 
		'except' 	=> 	array(
			'edit' 		=> 	array( 'buinessData' ),
			'add'		=> 	array( 'buinessData' ),
			'copy'		=> 	array( 'buinessData' ),
			'delete' 	=> 	array( 'buinessData' )
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
				'text' => __('Project list', true),
				'link' => '/projects/index'
			);

		if ($this->action != 'add')
			$tab[] = array (
				'text' => __('Add project', true),
				'link' => '/projects/add'
			);

		$tab[] = array (
			'text' => __('Display full history', true),
			'link' => '/deploymentLogs'
		);
		$this->set("context_menu", $tab);
	}// beforeRender

	// Public actions -----------------------------------------------------------
	/**
	 * List all projects
	 */
	function index() {
		$crumbs[] = array(
			'name' 		=> __('Projects', true),
			'link'		=> null,
			'options'	=> null
			);
		$this->set('crumbs', $crumbs);
		
		$this->set('data', $this->paginate('Project')); 
	}// index
	
	/**
	 * View properties of a specified project
	 * @param string $id ID of the project to be viewed
	 */
	function view($id = null) {
		
		if (!$id or !($project = $this->Project->read(null, $id))) {
			$this->Session->setFlash(__('Invalid id.', true));
			$this->redirect('/projects/index');
			exit();
		}
		$crumbs[] = array(
			'name' 		=> __('Projects', true),
			'link'		=> '/projects/index',
			'options'	=> null
			);
		$crumbs[] = array(
			'name' 		=> $project['Project']['name'],
			'link'		=> null,
			'options'	=> null
			);
		$this->set('crumbs', $crumbs);
		$this->set('project', $project);
		$this->set('deploymentMethod', $this->Project->getMethodName($project['Project']['method']));
	}// view

	/**
	 * Add a new project
	 */
	function add() {
		$crumbs[] = array(
			'name' 		=> __('Projects', true),
			'link'		=> '/projects/index',
			'options'	=> null
			);
		$crumbs[] = array(
			'name' 		=> __('New project', true),
			'link'		=> null,
			'options'	=> null
			);
		$this->set('crumbs', $crumbs);
		
		$this->_initializeLists();
		if (!empty ($this->data)) {
			if ($this->Project->save($this->data)) {
				$this->Session->setFlash(__('Project saved.', true));
				$this->redirect('/projects/view/'.$this->Project->getInsertID());
			} else {
				$this->Session->setFlash(__('Please correct errors below.', true));
			}
		}
	}// add 
	
	/**
	 * Add a new project
	 */
	function copy($id = null) {
		$this->_initializeLists();
		if (empty ($this->data)) {
			if (!$id or !($project = $this->Project->read(null, $id))) {
				$this->Session->setFlash(__('Invalid id.', true));
				$this->redirect('/projects/index');
			}
			
			$this->data = $project;
			$this->data['Project']['id'] = null;
			$crumbs[] = array(
				'name' 		=> __('Projects', true),
				'link'		=> '/projects/index',
				'options'	=> null
				);
			$crumbs[] = array(
				'name' 		=> __($project['Project']['name'], true),
				'link'		=> '/projects/view/'.$project['Project']['id'],
				'options'	=> null
				);
			$crumbs[] = array(
				'name' 		=> __('New project', true),
				'link'		=> null,
				'options'	=> null
				);
			$this->set('crumbs', $crumbs);
		} else {
			if ($this->Project->save($this->data)) {
				$this->Session->setFlash(__('Project saved.', true));
				$lastId = $this->Project->getLastInsertID();
				$this->redirect('/projects/view/' . $lastId);
			} else {
				$this->Session->setFlash(__('Please correct errors below.', true));
			}
		}
	}// copy 
	
	/**
	 * Edit a project properties
	 * @param string $id ID of the project to be edited 
	 */
	function edit($id = null) {
		$this->_initializeLists();
		if (empty ($this->data)) {
			if (!$id or !($project = $this->Project->read(null, $id))) {
				$this->Session->setFlash(__('Invalid id.', true));
				$this->redirect('/projects/index');
			}
			$this->data = $project;
			
		} else {
			if ($this->Project->save($this->data)) {
				$this->Session->setFlash(__('Project saved.', true));
				$this->redirect('/projects/view/' . $id);
			} else {
				$this->Session->setFlash(__('Please correct errors below.', true));
			}
		}
	}// edit
	
	/**
	 * Delete a project
	 * @param string $id ID of the project to be deleted
	 */
	function delete($id = null) {
		if (!$id or !$this->Project->read(null, $id)) {
			$this->Session->setFlash(__('Invalid id.', true));
			$this->redirect('/projects/index');
		}
		if ($this->Project->del($id)) {
			$this->Session->setFlash(__('Project deleted.', true));
			$this->redirect('/projects/index');
		}
	}// delete
	
	function _initializeLists() {
		$this->set('deploymentMethods', $this->Project->deploymentMethods);
	}// _initializeLists
	
}// ProjectsController
?>