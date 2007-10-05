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
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array (
			'text' => 'Actions'
		);
		if ($this->action != 'index')
			$tab[] = array (
				'text' => 'Liste des objets de contrôle',
				'link' => '/control_objects/index'
			);
		if ($this->action != 'add')
			$tab[] = array (
				'text' => 'Ajouter un objet de contrôle',
				'link' => '/control_objects/add'
			);
		$this->set('context_menu', $tab);
		parent :: beforeRender();
	}

	function index() {
		$criteria = NULL;
		list ($order, $limit, $page) = $this->Pagination->init($criteria);
		$this->set('data', $this->ControlObject->findAll($criteria, NULL, $order, $limit, $page, 0));
	}

	function view($id = null) {
		if (!$id or !$this->ControlObject->read(null, $id)) {
			$this->Session->setFlash('Identifiant invalide.');
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
				$this->Session->setFlash('Objet de contrôle créé.');
				$this->redirect('/control_objects/index');
			} else {
				$this->Session->setFlash('Veuillez corriger les erreurs ci-dessous.');
				$this->set('controlObjects', $this->ControlObject->generateList());
			}
		}
	}

	function edit($id = null) {
		if (empty ($this->data)) {
			if (!$id or !$this->ControlObject->read(null, $id)) {
				$this->Session->setFlash('Identifiant invalide.');
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
				$this->Session->setFlash('Objet de contrôle modifié.');
				$this->redirect('/control_objects/view/' . $id);
			} else {
				$this->Session->setFlash('Veuillez corriger les erreurs ci-dessous.');
				$this->set('controlObjects', $this->ControlObject->generateList());
			}
		}
	}

	function delete($id = null) {
		if (!$id or !$this->ControlObject->read(null, $id)) {
			$this->Session->setFlash('Identifiant invalide.');
			$this->redirect('/control_objects/index');
			exit;
		}
		if ($this->ControlObject->del($id)) {
			$this->Aclite->reloadsAcls('Aco');
			$this->Session->setFlash('Objet de contrôle supprimé.');
			$this->redirect('/control_objects/index');
		} else {
			$this->Session->setFlash('Erreur lors de la suppression.');
			$this->redirect('/control_objects/view/' . $id);
		}
	}

}
?>