<?php
/* SVN FILE: $Id$ */
/**
 * Component that enforces authorizations
 * 
 * PHP 4 and 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			app
 * @subpackage		app.controllers.components
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Component that enforces authorizations
 *
 * @package		aclite
 * @subpackage	aclite.components
 */
class AcliteComponent extends Object {

	var $name = 'Aclite';

	var $controller = null;

	var $components = array (
		'Acl',
		'Session',
		'RequestHandler'
	);

	var $lastCheckDetails = '';
	
	// Specal component's functions -----------------------------------------	
	/**
	 * If required ('Security.authorizationsDisabled'), authorizations are validated onload
	 */
	function startup(& $controller) {		
		// Is aclite globally disabled (see config)?
		if (Configure::read('Security.authorizationsDisabled')) {
			return;
		}
		
		// Init Aclite
		$this->controller = & $controller;
		
		// Callback to beforeAuthorize for dynamic authorizations   
		$this->Session->delete('dynamicGroup');
		if (method_exists($this->controller, $tmp = 'beforeAuthorize')) {
			$this->controller->{$tmp}();
		}
		
		// Init config
		$config = & AcliteComponent :: getConfig();
		// Once not twice 
		if ($config['done'] === true) {
			return;
		}
		
		// Setting groups for the view
		$groups = $config['groups'];
		$myGroups = Set::extract($groups,'{n}.name');
		$this->controller->set('myGroups',$myGroups);
		
		// Check GLOBAL access violation 
		if (isset ($this->controller->authGlobal)) {
			$parentName = get_parent_class($controller);
			$parentName = substr($parentName, 0 , strlen($parentName) - strlen('Controller'));
			$isAuthorized = $this->_checkAccess($parentName,$this->controller->authGlobal);
			if (!$isAuthorized) {
				$this->_displayAccessDeniedPage();
			}
		}
		
		// Check LOCAL access violation 
		if (isset ($this->controller->authLocal)) {
			$isAuthorized = $this->_checkAccess($config['controller'], $this->controller->authLocal);
			if (!$isAuthorized) {
				$this->_displayAccessDeniedPage();
			}
		}
	
		// Successfully authorized once
		$config['done'] = true;
	} // startup
	
	/**
	 * Build a config out of session variable and context data
	 * 
	 * @return array Provide information about the current user's groups and the requested ressource
 	 * @access public
	 */
	function & getConfig() {
		static $config = null;
		
		$user = $this->Session->read('User');
		$dynamicGroups = $this->Session->read('dynamicGroup');
		
		if (is_array($dynamicGroups) && is_array($user['Group']))  {
			$groups = array_merge ($user['Group'],$dynamicGroups);
		} else if (is_array($dynamicGroups)) {
			 $groups = $dynamicGroups;
		} else if (is_array($user['Group'])) {
			$groups = $user['Group'];
		} else {
			// groupe 'anonymous' si pas de groupe
			$groups[] = array('name' => 'anonymous');
		}
		if (!isset ($config) || !$config) {
			$config = array (
				'groups' 		=> $groups, 
				'controller' 	=> $this->controller->name,
				'action' 		=> $this->controller->action, 
				'done' 			=> false
			);
		}		
		
		return $config;
	}// getConfig
	
	/**
	 * Return a list of the current user's groups (static dynamic)
	 * 
	 * @return array List of the names of each group
 	 * @access public
	 */
	function getGroupsOfCurrrentUser() {
		$config = & AcliteComponent :: getConfig();
		$groups = $config['groups'];
		return Set::extract($groups,'{n}.name');
	}// getGroupsOfCurrrentUser
	
	// Public functions -----------------------------------------	
	/**
	 * Check if the current user is allowed to access a given ressource
	 * 
	 * @param array $requirements Requirements that protects the resource
 	 * @param array $redirectOptions What to do if the user is not allowed to continue (flash message or error page)
 	 * @access public
	 */
	function checkAccess( $requirements=array(), $redirectOptions=array() ) {
		$isAuthorized = $this->isAuthorized($requirements);
		if (!$isAuthorized){
			if (!is_array($redirectOptions)) {
				$redirectOptions = array('message' => $redirectOptions);
			}
			$mode = isset($redirectOptions['mode'])?$redirectOptions['mode']:'render';
			$this->_displayAccessDeniedPage($mode, $redirectOptions);
		}
	}// checkAccess
		
	/**
	 * Check if the current user is authoriez to access a given ressource
	 * 
	 * @param array $requirements Requirements that protects the resource
 	 * @return boolean true if allowed; false otherwise
 	 * @access public
	 */
	function isAuthorized($requirements=array()) {
		$res = true;
		$config = & AcliteComponent :: getConfig();
		$groups = $config['groups'];

		if (empty($requirements) || !is_array($requirements)) {
			// Bad array syntax
			$this->lastCheckDetails = 'Bad ACL syntax';
			return false;
		}
		foreach ($requirements as $requirement) {
			if (is_array($requirement)) {
				
				if (isset ($requirement['require'])) {
					$acos = $requirement['require'];
				} else {
					// Bad array syntax
					$this->lastCheckDetails = 'Missing require directive';
					return false;
				}
				if (isset ($requirement['action'])) {
					if (is_array($requirement['action']) && !empty ($requirement['action'])) {
						foreach ($requirement['action'] as $action) {
							$directive = " `$acos` [$action] with group(s) : ";
							foreach ($groups as $group) {
								$directive .= $group['name'].' ';
								$res = $this->Acl->check('group.'.$group['name'], $acos,$action);
								if ($res === true) {
									break;
								}
							}	
							if ($res === false) {
								$this->lastCheckDetails = "Authentication failure for $directive";
								return false;
							}
						} // foreach
					} else {
						$directive = " `$acos` [".$requirement['action']."] with group(s) : ";
						foreach ($groups as $group) {
							$directive .= $group['name'].' ';
							$res = $this->Acl->check('group.'.$group['name'], $acos,$requirement['action']);
							if ($res === true) {
								break;
							}
						}	
					}
				} else {
					$directive = " `$acos` with group(s) : ";
					foreach ($groups as $group) {
						$directive .= $group['name'].' ';
						$res = $this->Acl->check('group.'.$group['name'], $acos);
						if ($res === true) {
							break;
						}
					}	
				}
			} else {
				$directive = " `$requirement` with group(s) : ";
				foreach ($groups as $group) {
					$directive .= $group['name'].' ';
					$res = $this->Acl->check('group.'.$group['name'], $requirement);
					if ($res === true) {
						break;
					}
				}
			}
			if ($res === false) {
				$this->lastCheckDetails = "Authentication failure for $directive";
				return false;
			}
		} // foreach

		$this->lastCheckDetails = 'All requirements ended successfully';
		return true;
	}// isAuthorized
	
	/**
	 * Add a dynamic group to the current User's session
	 * 
	 * @param string $acceptedUsers List of accepted users
	 * @param string $dynamicGroup Dynamic group to be added
 	 * @access public
	 */
	function updateSessionWithDynamicGroup($acceptedUsers = null, $dynamicGroup = null){
		if (!isset($_SESSION['User']['User']['cn'])) {
			return;		
		}		
	
		$currentUser = trim(strtoupper($_SESSION['User']['User']['cn']));
		if (is_string($acceptedUsers)){
			$acceptedUsers = array($acceptedUsers);
		}
		
		foreach($acceptedUsers as $acceptedUser) {
			if ($currentUser == trim(strtoupper($acceptedUser))) {
				$this->Session->write('dynamicGroup', array( array( 'name' => $dynamicGroup )));				
			}
		}
	}// updateSessionWithDynamicGroup
	
	/**
	 * Ask the Acl model to reload the acl contained in the database
	 * 
	 * @see app/plugins/models/acl_management.php for further details
	 * @param string $type REload only a given type of Acl (Aro,Aco)
 	 * @access public
	 */
	function reloadsAcls($type = null) {
		App::import('Model', 'Aclite.AclManagement');
		$aclite = new AclManagement();
		$aclite->reloadAcls($type);
	}//reloadsAcls
	
	// Private functions -----------------------------------------
	function _checkAccess($controller, $authorizations = array()) {
		$config = & AcliteComponent :: getConfig();
		
		if (!empty($authorizations)) {
			
			if (isset ($authorizations[$controller])) {
				// Controller wide ACL
				$requireList = $authorizations[$controller];
				if (isset ($authorizations['except'])) {
					// Handling aurthorization exceptions
					if (isset ($authorizations['except'][$config['action']])) {
						$exceptList =  $authorizations['except'][$config['action']];
						return $this->isAuthorized($exceptList);
					} else if (isset ($authorizations['except'][$config['controller'].'.'.$config['action']])) {
						$exceptList = $authorizations['except'][$config['controller'].'.'.$config['action']];
						return $this->isAuthorized($exceptList);
					} else 
						// No usable exception found
						return $this->isAuthorized($requireList);
				} else {
					// No usable exception found
					return $this->isAuthorized($requireList);
				}
				
			} else {
				// Action specific ACL
				if (isset ($authorizations[$config['action']])) {
					return $this->isAuthorized($authorizations[$config['action']]);
				} else {
					$this->lastCheckDetails = 'No ACL directive found for current action';
					return true;
				}
			}
			
		} else {
			// No auth required
			$this->lastCheckDetails = 'No ACL directive found';
			return true;
		}
	}// checkAccess
		
	function _displayAccessDeniedPage($mode='render',$options=array()) {	
		if ($mode=='render') {
			$defaultOptions = array(
				'message' 	 	=> $this->lastCheckDetails,
				'view' 			=> null,
				'layout'		=> ($this->RequestHandler->isAjax()?'ajax':null)
			);		
			$options = am($defaultOptions, $options);
			extract($options);
			
			if (Configure::read('debug') === 0) {
				$message = __('Insufficient privileges for accessing the requested ressource', true);
			}
			
			$this->controller->set('denialDetails', $message);
			if (is_null($view)) {
				$file = APP . 'plugins/aclite/views/pages/denied.ctp';
			}
			$this->controller->render($view, $layout, $file);	
			return ;
			
		} else if ($mode=='redirect') {
			$defaultOptions = array(
				'url' 			=> '/',
				'message' 	 	=> $this->lastCheckDetails,
			);		
			$options = am($defaultOptions, $options);
			extract($options);
			
			$this->controller->Session->setFlash($message);
			$this->controller->redirect($url);
			exit ();
		} 
	}// displayAccessDeniedPage
	
} // Aclite
?>
