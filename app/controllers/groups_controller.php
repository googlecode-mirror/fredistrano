<?php
class GroupsController extends AppController {

	var $name = 'Groups';
	
	var $helpers = array (
		'fbollon'
	);
	
	var $uses = array ('Group','User');
		
	var $components = array (
		'RequestHandler'
	);

	var $authLocal = array (
		'Groups' => array (
			'authorizations'
		)
	);
	
	//configuration de la pagination	
	var $paginate = array(
		'limit' => 15,
		'format' => 'pages'
	);

	function beforeRender() {
		parent::beforeRender();
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array (
			'text' => 'Actions'
		);
		if ($this->action != 'index')
			$tab[] = array (
				'text' => __('Group list', true),
				'link' => '/groups/index'
			);
		if ($this->action != 'add')
			$tab[] = array (
				'text' => __('Add group', true),
				'link' => '/groups/add'
			);
		$this->set('context_menu', $tab);
	}

	function index() {
		$data = $this->paginate('Group');
		$this->set(compact('data'));
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
		$this->set('group', $this->Group->read(null, $id));
		// $this->set('users', $this->Group->getUsers($id));
		$this->set('person', $this->User->find('list', array('fields' => array('User.login', 'User.login'))));
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
			$this->render();
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
	}
	
}
?>