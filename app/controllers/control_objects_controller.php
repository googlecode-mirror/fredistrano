<?php
class ControlObjectsController extends AppController {

	var $name = 'ControlObjects';
	
	var $helpers = array (
	);
	
	var $components = array (
	);

//	var $authLocal = array (
//		'ControlObjects' => array (
//			'authorizations'
//		)
//	);

	function beforeRender() {
		parent :: beforeRender();
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array (
			'text' => 'Actions'
		);
		if ($this->action != 'index')
			$tab[] = array (
				'text' => LANG_CONTROLOBJECTLIST,
				'link' => '/control_objects/index'
			);
		if ($this->action != 'add')
			$tab[] = array (
				'text' => LANG_ADDCONTROLOBJECT,
				'link' => '/control_objects/add'
			);
		$this->set('context_menu', $tab);
	}

	function index() {
		$criteria = NULL;
		list ($order, $limit, $page) = $this->Pagination->init($criteria);
		$this->set('data', $this->ControlObject->findAll($criteria, NULL, $order, $limit, $page, 0));
	}

	function view($id = null) {
		if (!$id or !$this->ControlObject->read(null, $id)) {
			$this->Session->setFlash(LANG_INVALIDCREDENTIALS);
			$this->redirect('/control_objects/index');
			exit;
		}
		$this->set('controlObject', $this->ControlObject->read(null, $id));
	}

	function add() {
		if (empty ($this->data)) {
			$this->set('controlObjects', $this->ControlObject->generateList());
			$this->render();
		} else {
			$this->cleanUpFields();
			if ($this->ControlObject->save($this->data)) {
				$this->Aclite->reloadsAcls('Aco');
				$this->Session->setFlash(LANG_COCREATED);
				$this->redirect('/control_objects/index');
			} else {
				$this->Session->setFlash(LANG_CORRECTERRORSBELOW);
				$this->set('controlObjects', $this->ControlObject->generateList());
			}
		}
	}

	function edit($id = null) {
		if (empty ($this->data)) {
			if (!$id or !$this->ControlObject->read(null, $id)) {
				$this->Session->setFlash(LANG_INVALIDCREDENTIALS);
				$this->redirect('/control_objects/index');
				exit;
			}
			$this->data = $this->ControlObject->read(null, $id);
			$this->set('controlObjects', $this->ControlObject->generateList());
		} else {
			$this->cleanUpFields();
			if (empty ($this->data['ControlObject']['parent_id']))
				unset ($this->data['ControlObject']['parent_id']);
			if ($this->ControlObject->save($this->data)) {
				$this->Aclite->reloadsAcls('Aco');
				$this->Session->setFlash(LANG_COUPDATED);
				$this->redirect('/control_objects/view/' . $id);
			} else {
				$this->Session->setFlash(LANG_CORRECTERRORSBELOW);
				$this->set('controlObjects', $this->ControlObject->generateList());
			}
		}
	}

	function delete($id = null) {
		if (!$id or !$this->ControlObject->read(null, $id)) {
			$this->Session->setFlash(LANG_INVALIDCREDENTIALS);
			$this->redirect('/control_objects/index');
			exit;
		}
		if ($this->ControlObject->del($id)) {
			$this->Aclite->reloadsAcls('Aco');
			$this->Session->setFlash(LANG_CODELETED);
			$this->redirect('/control_objects/index');
		} else {
			$this->Session->setFlash(LANG_ERRORDURINGDELETION);
			$this->redirect('/control_objects/view/' . $id);
		}
	}

}
?>