<?php
class UsersController extends AppController {

	var $name = 'Users';

	var $helpers = array ();

	var $components = array ();

//	var $authLocal = array(
//		'Users' => array('authorizations'),
//		'except' => array (
//			'login'=> array('public'),
//			'logout'=> array('public')
//		)
//	);

	function beforeRender() {
		parent::beforeRender();
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array('text' => 'Actions');
		if ($this->action != 'index')
			$tab[] = array('text' => LANG_USERLIST, 'link' => '/users/index');
		if ($this->action != 'add')
			$tab[] = array('text' => LANG_USERADD, 'link' => '/users/add');
		$this->set("context_menu", $tab);
	}

	function index() {
		$criteria = NULL;
		list ($order, $limit, $page) = $this->Pagination->init($criteria);
		$data = $this->User->findAll($criteria, NULL, $order, $limit, $page);
		$this->User->_formatAll($data);
		$this->set('data', $data);
	}

	function view($id = null) {
		if (!$id or !$this->User->read(null, $id)) {
			$this->Session->setFlash('Identifiant invalide.');
			$this->redirect('/users/index');
			exit;
		}
		$user = $this->User->read(null, $id);
		$this->User->_format($user);
		$this->set('user', $user);
	}

	function add() {
		if (empty ($this->data)) {
			$this->set('groups', $this->User->Group->generateList());
			$this->set('selectedGroups', null);
			$this->render();
		} else {
			$this->cleanUpFields();
			if ($this->User->save($this->data)) {
				$this->Aclite->reloadsAcls('Aro');
				$this->Session->setFlash('Utilisateur créé.');
				$this->redirect('/users/index');
			} else {
				$this->Session->setFlash('Veuillez corriger les erreurs ci-dessous.');
				$this->set('groups', $this->User->Group->generateList());
				if (empty ($this->data['Group']['Group'])) {
					$this->data['Group']['Group'] = null;
				}
				$this->set('selectedGroups', $this->data['Group']['Group']);
			}
		}
	}

	function edit($id = null) {
		if (empty ($this->data)) {
			if (!$id or !$this->User->read(null, $id)) {
				$this->Session->setFlash('Identifiant invalide.');
				$this->redirect('/users/index');
				exit;
			}
			$this->data = $this->User->read(null, $id);
			$this->set('groups', $this->User->Group->generateList());
			if (empty ($this->data['Group'])) {
				$this->data['Group'] = null;
			}
			$this->set('selectedGroups', $this->_selectedArray($this->data['Group']));
		} else {
			$this->cleanUpFields();
			if ($this->User->save($this->data)) {
				$this->Aclite->reloadsAcls('Aro');
				$this->Session->setFlash('Utilisateur modifié.');
				$this->redirect('/users/view/' . $id);
			} else {
				$this->Session->setFlash('Veuillez corriger les erreurs ci-dessous.');
				$this->set('groups', $this->User->Group->generateList());
				if (empty ($this->data['Group']['Group'])) {
					$this->data['Group']['Group'] = null;
				}
				$this->set('selectedGroups', $this->data['Group']['Group']);
			}
		}
	}

	function delete($id = null) {
		if (!$id or !$this->User->read(null, $id)) {
			$this->Session->setFlash('Identifiant invalide.');
			$this->redirect('/users/index');
			exit;
		}
		if ($this->User->del($id)) {
			$this->Aclite->reloadsAcls('Aro');
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
			exit;
		}

		if (!empty ($this->data)) {
			$user = $this->User->read(null, $id);

			// le mot de passe n'est pas obligatoire donc l'ancien mdp peut être vide
			if (empty ($this->data['User']['password']) or empty ($this->data['User']['confirm_password'])) {
				$this->Session->setFlash('Veuillez remplir tous les champs.');
			}
			elseif (md5($this->data['User']['old_password']) != $user['User']['password']) {
				$this->Session->setFlash('Ancien mot de passe incorrect.');
			}
			elseif ($this->data['User']['password'] != $this->data['User']['confirm_password']) {
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
		// On accepte uniquement les requetes HTTPS
		if (!env('HTTPS') && _HTTPSENABLED) {
			$this->Session->setFlash('HTTPS required but unavailable!');
			$this->redirect('/');
			exit ();
		}

		// Un utilisateur a fourni ses identifiants
		if (!empty ($this->data)) {

			if ($this->User->isValid($this->data['User']['login'])) {
				// Un utilisateur portant le meme nom existe dans la base
				
				if ($this->_authenticate($this->data['User']['login'], $this->data['User']['password']) === true) {
					// Authentification réussie
					$someone = $this->User->findByLogin($this->data['User']['login']);
					$userSession['id'] = $someone['User']['id'];
					$userSession['login'] = $someone['User']['login'];
					if (empty ($someone['Group']))
						$someone['Group'][0]['name'] = 'member';
					$userSession['group'] = $someone['Group'][0]['name'];
					$this->Session->write('user_alias', $someone['User']['login']);
					$this->Session->write('User', $userSession);
					$this->log($someone['User']['login'] . " - Connexion", LOG_DEBUG);
					$this->Session->setFlash('Identification acceptée');

				} else {
					//Authentification échouée
					$this->Session->setFlash('Identification incorrecte !');
				}

			} else {
				// L'utilisateur n'existe pas dans la base
				$this->Session->setFlash('Utilisateur inconnu !');
			}

			// Affichage
            $tmp = empty($_SERVER['HTTP_REFERER'])?'/':$_SERVER['HTTP_REFERER'];
            if (_HTTPSENABLED == 2)
            	$tmp = preg_replace('#(http)://#','${1}s://',$tmp);
		    $this->set('HTTP_REFERER', $tmp);
			if (_HTTPSENABLED != 1)
            	$this->render('login_php','ajax');
            else
            	$this->render('login_js','ajax');
		}
	}

	function logout() {
		$this->log($this->Session->read('User.login') . " - Déconnexion", LOG_DEBUG);
		$this->Session->delete('User');
		$this->Session->delete('user_alias');
		$this->Session->setFlash('Vous êtes maintenant déconnecté.');
		$this->redirect($this->referer());
	}

	/**
	 * authentification par webservice + mysql
	 * pour changer le type d'authentification voir le fichier app/confg/config.php
	 */
	private function _authenticate($user, $passwd) {

		if (_AUTHENTICATIONTYPE == "1") {
			// authentification par WS
			include ("SOAP/Client.php");
			
			$dest = "https://" . _WEBSERVICESSERVER . "/OSI_authentificationWS/ConfigSSL?wsdl";
			$wsdl = new SOAP_WSDL($dest);
			$soapclient = $wsdl->getProxy();
			$soapclient->setOpt('curl', CURLOPT_CAINFO, _WS_SSL_TRUSTEDCA_FILE);
			$soapclient->setOpt('curl', CURLOPT_SSL_VERIFYPEER, 2);
			$soapclient->setOpt('curl', CURLOPT_SSL_VERIFYHOST, 0);
			
			$res = $soapclient->authentifierAnnuaire($user, $passwd, _DIRECTORYTYPE);
			return $res=="true";

		} else {
			//authentification par mysql
			$user = $this->User->findByLogin($user); // requete cachée
			
			return (!empty ($user['User']['password']) and ($user['User']['password'] == md5($passwd)));
		}
	} // _authenticate
}
?>