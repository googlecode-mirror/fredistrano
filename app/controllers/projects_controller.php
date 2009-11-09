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
	
	
	function beforeFilter(){
		parent::beforeFilter();
		
		$this->ContextMenu->addSection(__('Actions', true));
	}
	
	// Public actions -----------------------------------------------------------
	/**
	 * List all projects
	 */
	function index() {
		$this->Crumbs->leaf = __('Projects', true);
		
		$this->ContextMenu->addLink(__('Add project', true), '/projects/add');
		$this->ContextMenu->addLink(__('Display full history', true), '/deploymentLogs');
		
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
		
		$this->Crumbs->addLink(__('Projects', true), '/projects/index');
		$this->Crumbs->leaf = $project['Project']['name'];
		
		$this->ContextMenu->addLink(__('Project list', true), '/projects');
		$this->ContextMenu->addLink(__('Add project', true), '/projects/add');
		$this->ContextMenu->addLink(__('Display full history', true), '/deploymentLogs');
		
		$this->set('project', $project);
		$this->set('deploymentMethod', $this->Project->getMethodName($project['Project']['method']));
	}// view

	/**
	 * Add a new project
	 */
	function add() {
		$this->Crumbs->addLink(__('Projects', true), '/projects/index');
		$this->Crumbs->leaf = __('New project', true);
		
		$this->ContextMenu->addLink(__('Project list', true), '/projects');
		$this->ContextMenu->addLink(__('Display full history', true), '/deploymentLogs');
		
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
			
			$this->Crumbs->addLink(__('Projects', true), '/projects/index');
			$this->Crumbs->addLink($project['Project']['name'], '/projects/view/'.$project['Project']['id']);
			$this->Crumbs->leaf = __('New project', true);

			$this->ContextMenu->addLink(__('Project list', true), '/projects');
			$this->ContextMenu->addLink(__('Display full history', true), '/deploymentLogs');
			
			
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

			$this->Crumbs->addLink(__('Projects', true), '/projects/index');
			$this->Crumbs->addLink($project['Project']['name'], '/projects/view/'.$project['Project']['id']);
			$this->Crumbs->leaf = __('Edit project', true);

			$this->ContextMenu->addLink(__('Project list', true), '/projects');
			$this->ContextMenu->addLink(__('Add project', true), '/projects/add');
			$this->ContextMenu->addLink(__('Display full history', true), '/deploymentLogs');

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
	
	/**
	 * Projects list for autocomplete field
	 *
	 * @return void
	 * @author Frédéric BOLLON
	 */
	function projectsList(){
		Configure::write('debug', 0);
		$this->layout = 'ajax';
		$projects = $this->Project->find('list');
		
		$this->set('projects', $projects);
	}
	
	function _initializeLists() {
		$this->set('deploymentMethods', $this->Project->deploymentMethods);
	}// _initializeLists
	
}// ProjectsController
?>