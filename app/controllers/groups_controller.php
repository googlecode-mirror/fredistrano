<?php
class GroupsController extends AppController {

	var $name = 'Groups';
	var $helpers = array('Html', 'Form', 'Error', 'Pagination', 'Ajax', 'Javascript');
	var $components = array ('Pagination', 'RequestHandler');

	function beforeRender() {
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array('text' => 'Actions');

		if ($this->action != 'index')
			$tab[] = array('text' => 'Liste des groupes', 'link' => '/groups/index');

		if ($this->action != 'add')
			$tab[] = array('text' => 'Ajouter un groupe', 'link' => '/groups/add');

		$this->set("context_menu", $tab);
	}

	function index() {
		$criteria = NULL;
		list ($order, $limit, $page) = $this->Pagination->init($criteria); // Added
		$data = $this->Group->findAll($criteria, NULL, $order, $limit, $page); // Extra parameters added
		$this->set('data', $data);
	}

	function view($id = null) {
		if (!$id or !$this->Group->read(null, $id)) {
			$this->Session->setFlash('Identifiant invalide.');
			$this->redirect('/groups/index');
		}
		$this->set('group', $this->Group->read(null, $id));
	}

	function add() {
		if(empty($this->data)) {
			$this->render();
		} else {
			$this->cleanUpFields();
			if($this->Group->save($this->data)) {
				$this->Session->setFlash('Groupe créé.');
				$this->redirect('/groups/index');
			} else {
				$this->Session->setFlash('Veuillez corriger les erreurs ci-dessous.');
			}
		}
	}

	function edit($id = null) {
		if(empty($this->data)) {
			if (!$id or !$this->Group->read(null, $id)) {
				$this->Session->setFlash('Identifiant invalide.');
				$this->redirect('/groups/index');
			}
			$this->data = $this->Group->read(null, $id);
		} else {
			$this->cleanUpFields();
			if($this->Group->save($this->data)) {
				$this->Session->setFlash('Groupe modifié.');
				$this->redirect('/groups/view/' . $id);
			} else {
				$this->Session->setFlash('Veuillez corriger les erreurs ci-dessous.');
			}
		}
	}

	function delete($id = null) {
		if (!$id or !$this->Group->read(null, $id)) {
			$this->Session->setFlash('Identifiant invalide.');
			$this->redirect('/groups/index');
		}
		if($this->Group->del($id)) {
			$this->Session->setFlash('Groupe supprimé.');
			$this->redirect('/groups/index');
		} else {
			$this->Session->setFlash('Erreur lors de la suppression.');
			$this->redirect('/groups/view/' . $id);
		}
	}
}
?>