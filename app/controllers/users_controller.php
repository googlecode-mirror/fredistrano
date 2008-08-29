<?php
class UsersController extends AppController {

	var $name = 'Users';

	var $uses = array ('User', 'Group', 'Profile');	

	var $helpers = array ();

	var $components = array ();

	var $authLocal = array(
		'Users' => array('authorizations'),
		'except' => array (
			'login'				=> array('public'),
			'logout'			=> array('public'),
			'change_password'	=> array('password')
		)
	);
	
	var $paginate = array(
		'limit' => 10,
		'format' => 'pages'
	);
	
	function beforeAuthorize(){
		if ($this->params['action'] == 'change_password'){
			if (isset($_SESSION['User']['User']['id']) && ($_SESSION['User']['User']['id'] == $this->params['pass'][0])){
				$this->Session->write('dynamicGroup', array( array( 'name' => 'currentUser' )));
			}
		}
	}
	

	function beforeRender() {
		parent::beforeRender();
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array('text' => 'Actions');
		if ($this->action != 'index' && $this->action != 'change_password')
			$tab[] = array('text' => __('User list', true), 'link' => '/users/index');
		if ($this->action != 'add' && $this->action != 'change_password')
			$tab[] = array('text' => __('Add user', true), 'link' => '/users/add');
			
		$tab[] = array('text' => __('Manage groups', true), 'link' => '/groups/index');

		if (sizeof($tab)< 2)
				$tab = array();
		$this->set("context_menu", $tab);
	}

	function index() {
		$data = $this->paginate('User');
		$this->set(compact('data'));
	}

	function view($id = null) {
		if (!$id or !$this->User->read(null, $id)) {
			$this->Session->setFlash(__('Invalid id.', true));
			$this->redirect('/users/index');
			exit;
		}
		$user = $this->User->findById($id);
		$this->set('user', $user);
	}

	function add() {
		if (empty ($this->data)) {
			$this->set('groups', $this->Group->find('list'));
			$this->set('selectedGroups', null);
			$this->render();
		} else {
			if ($this->User->save($this->data)) {
				$this->Aclite->reloadsAcls('Aro');
				$this->Session->setFlash(__('The user has been created', true));
				$this->redirect('/users/index');
			} else {
				$this->Session->setFlash(__('Please correct errors below.', true));
				$this->set('groups', $this->Group->find('list'));
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
				$this->Session->setFlash(__('Invalid id.', true));
				$this->redirect('/users/index');
				exit;
			}
			$this->data = $this->User->read(null, $id);
			$this->set('groups', $this->Group->find('list'));
			if (empty ($this->data['Group'])) {
				$this->data['Group'] = null;
			}
		} else {
			if ($this->User->save($this->data)) {
				$this->Aclite->reloadsAcls('Aro');
				$this->Session->setFlash(__('The user has been updated', true));
				$this->redirect('/users/view/' . $id);
			} else {
				$this->Session->setFlash(__('Please correct errors below.', true));
				$this->set('groups', $this->Group->find('list'));
				if (empty ($this->data['Group']['Group'])) {
					$this->data['Group']['Group'] = null;
				}
				$this->set('selectedGroups', $this->data['Group']['Group']);
			}
		}
	}

	function delete($id = null) {
		if (!$id or !$this->User->read(null, $id)) {
			$this->Session->setFlash(__('Invalid id.', true));
			$this->redirect('/users/index');
			exit;
		}
		if ($this->User->del($id)) {
			$this->Aclite->reloadsAcls('Aro');
			$this->Session->setFlash(__('The user has been deleted', true));			
			$this->redirect('/users/index', null, true);
		} else {
			$this->Session->setFlash(__('Error during deletion.', true));
			$this->redirect('/users/view/' . $id);
		}
	}

	function settings(){
		// debug($_SESSION);

		$folder = new Folder(_FREDISTRANOPATH . DS . 'app' . DS . 'locale');
		$tmp = $folder->ls(true, array (".svn", ".", ".."));
		$availableLanguages = array ();
		foreach ($tmp[0] as $value) {
			$lang = substr($value, 0, 2);
			$availableLanguages[$lang] = $lang;
		}
		
		$this->set('availableLanguages', $availableLanguages);

		// debug($availableLanguages);
		
	}

	function change_password($id = null) {
		
		if (!$id or !$this->User->read(null, $id)) {
			$this->Session->setFlash(__('Invalid id.', true));
			$this->redirect('/users/index');
			exit;
		}

		if (!empty ($this->data)) {
			$user = $this->User->read(null, $id);

			// le mot de passe n'est pas obligatoire donc l'ancien mdp peut être vide
			if (empty ($this->data['User']['password']) or empty ($this->data['User']['confirm_password'])) {
				$this->Session->setFlash(__('Please fill in all fields.', true));
			}
			elseif (md5($this->data['User']['old_password']) != $user['User']['password']) {
				$this->Session->setFlash('Incorrect old password.');
			}
			elseif ($this->data['User']['password'] != $this->data['User']['confirm_password']) {
				$this->Session->setFlash('Not entered twice the same password');
			} else {
				$this->User->id = $user['User']['id'];
				if ($this->User->saveField('password', $this->data['User']['password'])) {
					$this->Session->setFlash(__('The password has been changed.', true));
					if (isset($_SESSION['dynamicGroup']) && $_SESSION['dynamicGroup'][0]['name'] == 'currentUser') {
						$this->redirect('/', null, true);
					}
					$this->redirect('/users/view/' . $id);
				} else {
					$this->Session->setFlash(__('Error during update : ', true) . mysql_error());
				}
			}
		}
	}

	function login() {
		// We only accept HTTPS requests
		if (!env('HTTPS') && Configure::read('Security.https')) {
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
						$someone['Group'][] = array('name' => 'member');
						
					foreach ($someone['Group'] as $group) {
							if($group['name'] == 'admin')
								$this->Session->write('isAdmin', true);
							if($group['name'] == 'premium')
								$this->Session->write('isPremium', true);
					}

					$this->Session->write('User', $someone);
					$this->log($this->data['User']['login'] . " - Connexion", LOG_DEBUG);
					$this->Session->setFlash(__('Identification accepted', true));
				} else {
					//Authentification failed
					$this->Session->setFlash(__('Invalid credentials', true));
				}

			} else {
				// The use doesn't exist
				$this->Session->setFlash(__('Invalid credentials', true));
			}

			// Affichage
            $tmp = empty($_SERVER['HTTP_REFERER'])?'/':$_SERVER['HTTP_REFERER'];
            if (Configure::read('Security.https') == 2)
            		$tmp = preg_replace('/(http):\/\//','${1}s://',$tmp);
		    		
            $this->redirect($tmp);
            exit;		
		}
	}

	function logout() {
		$this->log($this->Session->read('User.login') . " - Déconnexion", LOG_DEBUG);
		$this->Session->delete('User');
		$this->Session->delete('isAdmin');
		$this->Session->delete('isPremium');
		$this->Session->delete('user_alias');
		$this->Session->setFlash(__('Your are now disconnected.', true));
		$this->redirect($this->referer());
		exit;
	}

	/**
	 * authentification par webservice + mysql
	 * pour changer le type d'authentification voir le fichier app/confg/config.php
	 */
	private function _authenticate($user, $passwd) {

		if (Configure::read('Security.Authentication.type') == 0){
			// Accept all
			return true;
			
		} else if (Configure::read('Security.Authentication.type') == 1) {
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

		} else if (Configure::read('Security.Authentication.type') == 2) {
			// MySQL authentification 
			$user = $this->User->findByLogin($user); // requete cachée
			
			return (!empty ($user['User']['password']) and ($user['User']['password'] == md5($passwd)));
			
		} else {
			return false;
			
		}
	} // _authenticate
	
}// UsersController
?>