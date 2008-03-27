<?php
class UsersController extends AppController {

	var $name = 'Users';

	var $helpers = array ();

	var $components = array ();

	var $authLocal = array(
		'Users' => array('authorizations'),
		'except' => array (
			'login'=> array('public'),
			'logout'=> array('public')
		)
	);

	function beforeRender() {
		parent::beforeRender();
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array('text' => 'Actions');
		if ($this->action != 'index' && $this->action != 'change_password')
			$tab[] = array('text' => __('User list'), 'link' => '/users/index');
		if ($this->action != 'add' && $this->action != 'change_password')
			$tab[] = array('text' => __('Add user'), 'link' => '/users/add');

		if (sizeof($tab)< 2)
			$tab = array();
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
			$this->Session->setFlash(LANG_INVALIDID);
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
				$this->Session->setFlash(LANG_USERCREATED);
				$this->redirect('/users/index');
			} else {
				$this->Session->setFlash(LANG_CORRECTERRORSBELOW);
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
				$this->Session->setFlash(LANG_INVALIDID);
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
				$this->Session->setFlash(LANG_USERUPDATED);
				$this->redirect('/users/view/' . $id);
			} else {
				$this->Session->setFlash(LANG_CORRECTERRORSBELOW);
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
			$this->Session->setFlash(LANG_INVALIDID);
			$this->redirect('/users/index');
			exit;
		}
		if ($this->User->del($id)) {
			$this->Aclite->reloadsAcls('Aro');
			$this->Session->setFlash(LANG_USERDELETED);
			$this->redirect('/users/index');
		} else {
			$this->Session->setFlash(LANG_ERRORDURINGDELETION);
			$this->redirect('/users/view/' . $id);
		}
	}

	function change_password($id = null) {
		
		if (!$id or !$this->User->read(null, $id)) {
			$this->Session->setFlash(LANG_INVALIDID);
			$this->redirect('/users/index');
			exit;
		}
		
		if ($_SESSION['User']['id'] != $id) {
			$this->Session->setFlash(LANG_INVALIDID);
			$this->redirect('/');
			exit;
		}

		if (!empty ($this->data)) {
			$user = $this->User->read(null, $id);

			// le mot de passe n'est pas obligatoire donc l'ancien mdp peut être vide
			if (empty ($this->data['User']['password']) or empty ($this->data['User']['confirm_password'])) {
				$this->Session->setFlash(LANG_PLEASEFILLINALLFIELDS);
			}
			elseif (md5($this->data['User']['old_password']) != $user['User']['password']) {
				$this->Session->setFlash(LANG_INCORRECTOLDPASSWORD);
			}
			elseif ($this->data['User']['password'] != $this->data['User']['confirm_password']) {
				$this->Session->setFlash(LANG_NOTENTEREDTWICETHESAMEPASSWORD);
			} else {
				$this->User->id = $user['User']['id'];
				if ($this->User->saveField('password', $this->data['User']['password'], true)) {
					$this->Session->setFlash(LANG_PASSWORDCHANGED);
					$this->redirect('/users/view/' . $id);
				} else {
					$this->Session->setFlash(LANG_ERRORDURINGDELETION . mysql_error());
				}
			}
		}
	}

	function login() {
		// We only accept HTTPS requests
		if (!env('HTTPS') && Configure::read('httpsenabled')) {
			$this->Session->setFlash(__('HTTPS required but unavailable', true));
			$this->redirect('/');
			exit ();
		}

		// login form submited
		if (!empty ($this->data)) {

			if ($this->User->isValid($this->data['User']['login'])) {
				// login already exist
				
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
					$this->Session->setFlash(__('Identification accepted', true));

				} else {
					//Authentification échouée
					$this->Session->setFlash(__('Invalid credentials', true));
				}

			} else {
				// L'utilisateur n'existe pas dans la base
				$this->Session->setFlash(__('Invalid credentials', true));
			}

			// Affichage
            $tmp = empty($_SERVER['HTTP_REFERER'])?'/':$_SERVER['HTTP_REFERER'];
            if (Configure::read('httpsenabled') == 2)
            		$tmp = preg_replace('/(http):\/\//','${1}s://',$tmp);
		    		
		
            $this->redirect($tmp);
            exit;		
		}
	}

	function logout() {
		$this->log($this->Session->read('User.login') . " - Déconnexion", LOG_DEBUG);
		$this->Session->delete('User');
		$this->Session->delete('user_alias');
		$this->Session->setFlash(__('Your are now disconnected.', true));
		$this->redirect('/');
	}

	/**
	 * authentification par webservice + mysql
	 * pour changer le type d'authentification voir le fichier app/confg/config.php
	 */
	private function _authenticate($user, $passwd) {

		if (Configure::read('Authentication.type') == 0){
			// Accept all
			return true;
			
		} else if (Configure::read('Authentication.type') == 1) {
			// WS authentification 
			
			$client = new SoapClient( null,
			array(
				 		'location' 		=>	"https://" . _WEBSERVICESSERVER . "/OSI_authentificationWS/ConfigSSL?style=document",
				    	'uri'  			=>	'urn:OSI_authentificationWSVi',
	                    'use'     		=>	SOAP_LITERAL
			)
			);
				
			$params = array (
			new SoapParam( $user, 'login'),
			new SoapParam( $passwd, 'pass'),
			new SoapParam( _AUTHENTICATIONTDIRECTORY, 'annuaire')
			);

			try {
				$result = $client->__soapCall('authentifierAnnuaire', $params);
			} catch (SoapFault $fault) {
				$this->Session->setFlash('Identification impossible [err:'.$fault->getMessage().']');
				return false;
			}
			 
			return $result=='true';

		} else if (Configure::read('Authentication.type') == 2) {
			// MySQL authentification 
			$user = $this->User->findByLogin($user); // requete cachée
			
			return (!empty ($user['User']['password']) and ($user['User']['password'] == md5($passwd)));
			
		} else {
			return false;
			
		}
	} // _authenticate
	
}// UsersController
?>