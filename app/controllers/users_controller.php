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
class UsersController extends AppController {

	var $name = 'Users';

	var $uses = array ('User', 'Group', 'Profile');	

	var $authLocal = array(
		'Users' => array('authorizations'),
		'except' => array (
			'login'				=> array('public'),
			'logout'			=> array('public'),
			'change_password'	=> array('password'),
			'settings'	=> array('password')
		)
	);
	
	var $paginate = array(
		'limit' => 10,
		'format' => 'pages'
	);
	
	function beforeAuthorize(){
		if (in_array($this->params['action'], array ('settings', 'change_password'))){
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
		} else {
						
			if ($this->User->save($this->data)) {

				$this->data['Profile']['user_id'] = $this->User->id;
				$this->User->Profile->save($this->data);

				$this->Aclite->reloadsAcls('Aro');
				$this->Session->setFlash(__('The user has been created', true));
				$this->redirect('/users/view/'.$this->User->getInsertID());
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

	function settings($id = null){
		$folder = new Folder(APP.'locale');
		$tmp = $folder->ls(true, array (".svn", ".", ".."));
		$availableLanguages = array ();
		foreach ($tmp[0] as $value) {
			$lang = substr($value, 0, 2);
			$availableLanguages[$lang] = $lang;
		}
		
		if (empty ($this->data)) {
			if (!$id or !$this->User->read(null, $id)) {
				$this->Session->setFlash(__('Invalid id.', true));
				$this->redirect($this->referer());
				exit;
			}
			$this->data = $this->User->read(null, $id);
		} else {
			$this->User->Profile->save($this->data);
			$this->Session->write('User.Profile.lang', $this->data['Profile']['lang']);
			$this->Session->setFlash(__('Settings saved.', true));
			$this->redirect($this->referer());
			exit;
		}
		
		$this->set('availableLanguages', $availableLanguages);
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
				$this->Session->setFlash(__('Incorrect old password.', true));
			}
			elseif ($this->data['User']['password'] != $this->data['User']['confirm_password']) {
				$this->Session->setFlash(__('Not entered twice the same password', true));
			} else {
				$this->User->id = $user['User']['id'];
				if ($this->User->saveField('password', $this->data['User']['password'])) {
					$this->Session->setFlash(__('The password has been changed.', true));
					if (isset($_SESSION['dynamicGroup']) && $_SESSION['dynamicGroup'][0]['name'] == 'currentUser') {
						$this->redirect('/', null, true);
					}
					$this->redirect($this->referer());
				} else {
					$this->Session->setFlash(__('Error during update : ', true) . mysql_error());
				}
			}
		}
	}// change_password

	function login() {
		// login form submited
		if (empty ($this->data)) {
			$this->redirect($this->referer());
			exit;
		}
		
		if ($this->User->isValid($this->data['User']['login'])) {
			// login already exist
			
			if ($this->User->authenticate($this->data['User']['login'], $this->data['User']['password']) === true) {
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

		// Return to previous page
		$this->redirect($this->referer());
		exit;
	}// login

	function logout() {
		$this->log($this->Session->read('User.login') . " - ".__('logout', true), LOG_DEBUG);
		$this->Session->delete('User');
		$this->Session->delete('isAdmin');
		$this->Session->delete('isPremium');
		$this->Session->delete('user_alias');
		$this->Session->setFlash(__('Your are now disconnected.', true));
		$this->redirect($this->referer());
		exit;
	}// logout
	
}// UsersController
?>