<?php
class UsersController extends AppController {

	var $name = 'Users';
	var $helpers = array ('Html', 'Form', 'Error', 'Pagination', 'Ajax', 'Javascript');
	var $components = array ('Pagination', 'RequestHandler');

	function beforeRender() {
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array('text' => 'Actions');

		if ($this->action != 'index')
			$tab[] = array('text' => 'Liste des utilisateurs', 'link' => '/users/index');

		if ($this->action != 'add')
			$tab[] = array('text' => 'Ajouter un utilisateur', 'link' => '/users/add');

		$this->set("context_menu", $tab);
	}

	function index() {
		$criteria = NULL;
		list ($order, $limit, $page) = $this->Pagination->init($criteria); // Added
		$data = $this->User->findAll($criteria, NULL, $order, $limit, $page); // Extra parameters added
		$this->set('data', $data);
	}

	function view($id = null) {
		if (!$id or !$this->User->read(null, $id)) {
			$this->Session->setFlash('Identifiant invalide.');
			$this->redirect('/users/index');
		}
		$this->set('user', $this->User->read(null, $id));
	}

	function add() {
		if (empty ($this->data)) {
			$this->render();
		} else {
			$this->cleanUpFields();
			if ($this->User->save($this->data)) {
				$this->Session->setFlash('Utilisateur créé, pensez à gérer ses permissions.');
				$this->redirect('/users/index');
			} else {
				$this->Session->setFlash('Veuillez corriger les erreurs ci-dessous.');
			}
		}
	}

	function edit($id = null) {
		if (empty ($this->data)) {
			if (!$id or !$this->User->read(null, $id)) {
				$this->Session->setFlash('Identifiant invalide.');
				$this->redirect('/users/index');
			}
			$this->data = $this->User->read(null, $id);
		} else {
			$this->cleanUpFields();
			if ($this->User->save($this->data)) {
				$this->Session->setFlash('Utilisateur modifié.');
				$this->redirect('/users/view/' . $id);
			} else {
				$this->Session->setFlash('Veuillez corriger les erreurs ci-dessous.');
			}
		}
	}

	function delete($id = null) {
		if (!$id or !$this->User->read(null, $id)) {
			$this->Session->setFlash('Identifiant invalide.');
			$this->redirect('/users/index');
		}
		if ($this->User->del($id)) {
			$this->Session->setFlash('Utilisateur supprimé.');
			$this->redirect('/users/index');
		} else {
			$this->Session->setFlash('Erreur lors de la suppression.');
			$this->redirect('/users/view/' . $id);
		}
	}

	function change_password($id = null) {
		if (!$id or !$this->User->read(null, $id)) {
			$this->Session->setFlash('Identifiant invalide.');
			$this->redirect('/users/index');
		}

		if (!empty($this->data)) {
			$user = $this->User->read(null, $id);

			if (empty($this->data['User']['old_password'])
			or empty($this->data['User']['password'])
			or empty($this->data['User']['confirm_password'])) {
				$this->Session->setFlash('Veuillez remplir tous les champs.');
			} elseif (md5($this->data['User']['old_password']) != $user['User']['password']) {
				$this->Session->setFlash('Ancien mot de passe incorrect.');
			} elseif ($this->data['User']['password'] != $this->data['User']['confirm_password']) {
				$this->Session->setFlash('Vous n\'avez pas saisi deux fois le même mot de passe.');
			} else {
				$this->User->id = $user['User']['id'];
				if ($this->User->saveField('password', $this->data['User']['password'], true)) {
					$this->Session->setFlash('Mot de passe modifié.');
					$this->redirect('/users/view/' . $id);
				} else {
					$this->Session->setFlash('Erreur lors de la modification : ' . mysql_error());
				}
			}
		}
	}

	function login() {
		//Don't show the error message if no data has been submitted.
		$this->set('error', false);

		// If a user has submitted form data:
		if (!empty($this->data)) {
			$someone = $this->User->findByUsername($this->data['User']['username']);

			//authentification par webservice + mysql
			//pour changer le type d'authentification voir le fichier app/confg/config.php
			if (_AUTHENTICATIONTYPE == "1") {
				include ("SOAP/Client.php");
				unset ($ret);
				$dest = "http://" . _WEBSERVICESSERVER . "/OSI_authentificationWS/Config1?wsdl";
				$wsdl = new SOAP_WSDL($dest);
				$soapclient = $wsdl->getProxy();
				$ret = $soapclient->authentifierAnnuaire($someone['User']['username'], $this->data['User']['password'], _DIRECTORYTYPE);
			//authentification par mysql
			} else {
				$ret = (!empty($someone['User']['password']) and ($someone['User']['password'] == md5($this->data['User']['password'])));
			}

			//Authentification réussie
			if ($ret == "true") {
				// on ne met pas le mot de passe en session
				unset($someone['User']['password']);
				$this->Session->write('User', $someone['User']);

				$this->log("connexion " . $someone['User']['username'], LOG_DEBUG);

				$this->Session->setFlash('Identification acceptée.');
				$this->redirect('/');
			//Authentification échouée
			} else {
				// Remember the $error var in the view? Let's set that to true:
				$this->Session->setFlash('Identification incorrecte !');
				$this->redirect('/');
			}
		}
	}

	function logout() {
		$this->Session->delete('User');
		$this->Session->setFlash('Vous êtes maintenant déconnecté.');
		$this->redirect('/');
	}
}
?>