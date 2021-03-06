<?php
/* SVN FILE: $Id$ */
/**
 * Controller that provides access to some of the user management features
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
 * Controller that provides access to some of the user management features
 *
 * @package		app
 * @subpackage	app.controllers
 */
class GroupsController extends AppController {

	var $name = 'Groups';
	
	var $helpers = array ('fbollon');
	
	var $uses = array ('Group','User');
	
	var $authLocal = array (
		'Groups' => array (
			'authorizations'
		)
	);
	
	//configuration de la pagination	
	var $paginate = array('limit' => 15,'format' => 'pages');

	function beforeRender() {
		parent::beforeRender();

		$this->ContextMenu->addSection(__('Actions', true));

		if ($this->action != 'index'){
			$this->ContextMenu->addLink(__('Group list', true), '/groups/index');
		}
		if ($this->action != 'add'){
			$this->ContextMenu->addLink(__('Add group', true), '/groups/add');
		}

	}

	function index() {
		$data = $this->paginate('Group');
		$this->set(compact('data'));
		
		$this->Crumbs->addLink(__('Administration', true), '/administration');
		$this->Crumbs->leaf = __('Groups', true);
	}

	/**
	 * Affichage d'un groupe
	 *
	 * @param string $id 
	 * @return void
	 * @author Frédéric BOLLON
	 */	
	function view($id = null) {
		if (!$id or !$this->Group->read(null, $id)) {
			$this->Session->setFlash(__('Invalid id.', true));
			$this->redirect('/groups/index');
			exit;
		}
		// $this->Group->recursive = 0;
		$this->set('group', $group = $this->Group->read(null, $id));
		// $this->set('users', $this->Group->getUsers($id));
		$this->set('person', $this->User->find('list', array('fields' => array('User.login', 'User.login'))));

		$this->Crumbs->addLink(__('Administration', true), '/administration');
		$this->Crumbs->addLink(__('Groups', true), '/groups/index');
		$this->Crumbs->leaf = $group['Group']['name'];
	}

	/**
	 * Ajout d'un groupe
	 *
	 * @return void
	 * @author Frédéric BOLLON
	 */
	function add() {
		if (empty ($this->data)) {
			$this->set('groups', $this->Group->find('list'));
		} else {
			if ($this->Group->save($this->data)) {
				$this->Aclite->reloadsAcls('Aro');
				$this->Session->setFlash(__('The group has been created.', true));
				$this->redirect('/groups/index');
			} else {
				$this->Session->setFlash(__('Please correct errors below.', true));
				$this->set('groups', $this->Group->find('list'));
			}
		}
		
		$this->Crumbs->addLink(__('Administration', true), '/administration');
		$this->Crumbs->addLink(__('Groups', true), '/groups/index');
		$this->Crumbs->leaf = __('New group', true);
	}
	
	/**
	 * Modification d'un groupe
	 *
	 * @param string $id 
	 * @return void
	 * @author Frédéric BOLLON
	 */
	function edit($id = null) {
		if (empty ($this->data)) {
			if (!$id or !$this->Group->read(null, $id)) {
				$this->Session->setFlash(__('Invalid id.', true));
				$this->redirect('/groups/index');
				exit;
			}
			$this->data = $this->Group->read(null, $id);
			$this->set('groups', $this->Group->find('list'));
		} else {
			if ($this->Group->save($this->data)) {
				$this->Aclite->reloadsAcls('Aro');
				$this->Session->setFlash(__('The group has been updated.', true));
				$this->redirect('/groups/view/' . $id);
			} else {
				$this->Session->setFlash(__('Please correct errors below.', true));
				$this->set('groups', $this->Group->find('list'));
			}
		}
		
		$this->Crumbs->addLink(__('Administration', true), '/administration');
		$this->Crumbs->addLink(__('Groups', true), '/groups/index');
		$this->Crumbs->leaf = $this->data['Group']['name'];
	}
	
	/**
	 * Suppression d'un groupe
	 *
	 * @param string $id 
	 * @return void
	 * @author Frédéric BOLLON
	 */	
	function delete($id = null) {
		if (!$id or !$this->Group->read(null, $id)) {
			$this->Session->setFlash(__('Invalid id.', true));
			$this->redirect('/groups/index');
			exit;
		}
		if ($this->Group->del($id)) {
			$this->Aclite->reloadsAcls('Aro');
			$this->Session->setFlash(__('The group has been deleted.', true));
			$this->redirect('/groups/index');
		} else {
			$this->Session->setFlash(__('Error during deletion.', true));
			$this->redirect('/groups/view/' . $id);
		}
	}
	
	/**
	 * Affectation des utilisateurs aux groupes
	 *
	 * @param string $id 
	 * @return void
	 * @author Frédéric BOLLON
	 */
	function affectUsers($id = null){
		if (empty ($this->data)) {
			if (!$id or !$this->Group->read(null, $id)) {
				$this->Session->setFlash(__('Invalid id.', true));
				$this->redirect('/groups/index');
			}
				
			$group = $this->Group->find('first', array('id' => $id));
			$personList = $this->User->find('list', array('fields' => array('User.id', 'User.login')));

			$members = array();
			if (!empty($group['User'])) {
				$members = array_combine(
								Set::extract($group, 'User.{n}.id'),
								Set::extract($group, 'User.{n}.login')
								);
			}
			
			$personList = array_diff($personList, $members);
			$this->set('group', $group);
			$this->set('personList',$personList);
			$this->set('members', $members);
				
		}else {
			if ($this->Group->updateMembership($this->data)){
				$this->Session->setFlash(__('Group updated', true));
			}else{
				$this->Session->setFlash(__('Error during update.', true));
			}

			$this->redirect('/groups/view/'.$id);
			exit;
		}
		
		$this->Crumbs->addLink(__('Administration', true), '/administration');
		$this->Crumbs->addLink(__('Groups', true), '/groups/index');
		$this->Crumbs->leaf = __('Manage group members for ', true).$group['Group']['name'];
	}
	
}// Group
?>