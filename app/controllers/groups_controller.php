<?php
class GroupsController extends AppController {

	var $name = 'Groups';
	
	var $helpers = array (
	);
	
	var $components = array (
	);

	var $authLocal = array (
		'Groups' => array (
			'authorizations'
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
				'text' => LANG_GROUPLIST,
				'link' => '/groups/index'
			);
		if ($this->action != 'add')
			$tab[] = array (
				'text' => LANG_ADDGROUP,
				'link' => '/groups/add'
			);
		$this->set('context_menu', $tab);
	}

	function index() {
		$criteria = NULL;
		list ($order, $limit, $page) = $this->Pagination->init($criteria);
		$this->set('data', $this->Group->findAll($criteria, NULL, $order, $limit, $page, 0));
	}

	function view($id = null) {
		if (!$id or !$this->Group->read(null, $id)) {
			$this->Session->setFlash(LANG_INVALIDID);
			$this->redirect('/groups/index');
			exit;
		}
		$this->Group->recursive = 0;
		$this->set('group', $this->Group->read(null, $id));
	}

	function add() {
		if (empty ($this->data)) {
			$this->set('groups', $this->Group->generateList());
			$this->render();
		} else {
			$this->cleanUpFields();
			if ($this->Group->save($this->data)) {
				$this->Aclite->reloadsAcls('Aro');
				$this->Session->setFlash(LANG_GROUPCREATED);
				$this->redirect('/groups/index');
			} else {
				$this->Session->setFlash(LANG_CORRECTERRORSBELOW);
				$this->set('groups', $this->Group->generateList());
			}
		}
	}

	function edit($id = null) {
		if (empty ($this->data)) {
			if (!$id or !$this->Group->read(null, $id)) {
				$this->Session->setFlash(LANG_INVALIDID);
				$this->redirect('/groups/index');
				exit;
			}
			$this->data = $this->Group->read(null, $id);
			$this->set('groups', $this->Group->generateList());
		} else {
			$this->cleanUpFields();
			if (empty ($this->data['Group']['parent_id']))
				unset ($this->data['Group']['parent_id']);
			if ($this->Group->save($this->data)) {				
				$this->Aclite->reloadsAcls('Aro');
				$this->Session->setFlash(LANG_GROUPUPDATED);
				$this->redirect('/groups/view/' . $id);
			} else {
				$this->Session->setFlash(LANG_CORRECTERRORSBELOW);
				$this->set('groups', $this->Group->generateList());
			}
		}
	}

	function delete($id = null) {
		if (!$id or !$this->Group->read(null, $id)) {
			$this->Session->setFlash(LANG_INVALIDID);
			$this->redirect('/groups/index');
			exit;
		}
		if ($this->Group->del($id)) {
			$this->Aclite->reloadsAcls('Aro');
			$this->Session->setFlash(LANG_GROUPDELETED);
			$this->redirect('/groups/index');
		} else {
			$this->Session->setFlash(LANG_ERRORDURINGDELETION);
			$this->redirect('/groups/view/' . $id);
		}
	}
}
?>