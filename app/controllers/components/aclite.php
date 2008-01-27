<?php
class AcliteComponent extends Object {

	var $name = 'Aclite';

	var $controller = null;

	var $components = array (
		'Acl',
		'Session'
	);

	function startup(& $controller) {		
		if (defined('_DISABLEACLITE') && _DISABLEACLITE) 
			return true;
			
		// Init
		$this->controller = & $controller;
		$config = & AcliteComponent :: getConfig();
		if ($config['done'] === true)
			return ;
		
		// Check current user
		if (empty($config['user'])) 
			// No user in session
			$config['user'] = 'group.anonymous';

		$authResult = array(
			'status' => true,
			'details' => 'No ACL directive found'
		);
		
		// Check GLOBAL access violation 
		if (isset ($this->controller->authGlobal)) {
			$parentName = get_parent_class($controller);
			$parentName = substr($parentName, 0 , strlen($parentName) - strlen('Controller'));
			$authResult = $this->_checkAccess($parentName,$this->controller->authGlobal);
			
			if ($authResult['status'] === false)
				$this->_returnDeniedResult($authResult);
		}
		
		// Check LOCAL access violation 
		if (isset ($this->controller->authLocal)) {
			$authResult = $this->_checkAccess($config['controller'], $this->controller->authLocal);
		
			if ($authResult['status'] === false)
				$this->_returnDeniedResult($authResult);
		}

		if ($authResult['status'] === false)
			$this->_returnDeniedResult($authResult);
			
		$config['done'] = true;
	} // startup

	function & getConfig() {
		static $config = null;

		if (!isset ($config) || !$config) {
			$config = array (
				'user' => $this->Session->read('user_alias'	), 
				'controller' => $this->controller->name,
				'action' => $this->controller->action, 
				'done' => false
			);
		}
		return $config;
	}// getConfig
	
	function checkAccess($acos, $action) {
		$config = & AcliteComponent :: getConfig();
		
		return $this->Acl->check($config['user'], $acos, $action);
	}// checkAccess
		
	function reloadsAcls($type = null) {
		loadPluginModels('aclite');
		$aclite = new AclManagement();
		$aclite->reloadAcls($type);
	}//reloadsAcls
	
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
						return $this->_checkAccessRequirements($exceptList);
					} else if (isset ($authorizations['except'][$config['controller'].'.'.$config['action']])) {
						$exceptList = $authorizations['except'][$config['controller'].'.'.$config['action']];
						return $this->_checkAccessRequirements($exceptList);
					} else 
						// No usable exception found
						return $this->_checkAccessRequirements($requireList);
				} else {
					// No usable exception found
					return $this->_checkAccessRequirements($requireList);
				}
				
			} else {
				// Action specific ACL
				if (isset ($authorizations[$config['action']])) {
					return $this->_checkAccessRequirements($authorizations[$config['action']]);
				} else {
					return array (
						'status' => true,
						'details' => 'No ACL directive found for current action'
					);
				}
			}
			
		} else {
			// No auth required
			return array (
				'status' => true,
				'details' => 'No ACL directive found'
			);
		}
	} // checkAccess
	
	function _checkAccessRequirements($requirements=array()) {
		$res = true;
		$config = & AcliteComponent :: getConfig();
		$user = $config['user'];

		if (empty($requirements) || !is_array($requirements)) {
			return array (
				'status' => false,
				'details' => 'Bad ACL syntax'
			);
		}

		foreach ($requirements as $requirement) {
			if (is_array($requirement)) {
				if (isset ($requirement['require']))
					$acos = $requirement['require'];
				else
					// Bad array syntax
					return array (
						'status' => false,
						'details' => 'Missing require directive'
					);

				if (isset ($requirement['action']))
					if (is_array($requirement['action']) && !empty ($requirement['action']))
						foreach ($requirement['action'] as $action) {
							//echo '0.' . $user . ' - ' . $acos . ' - ' . $action . '<br/>';
							$res = $this->Acl->check($user, $acos, $action);

							if ($res === false)
								return array (
									'status' => false,
									'details' => "Authentication failure for '$user' on '$acos' with '$action'"
								);
						} else {
						//echo '1.' . $user . ' - ' . $acos . ' - ' . $requirement['action'] . '<br/>';
						$directive = "'$user' on '$acos' with '" . $requirement['action']."'";
						$res = $this->Acl->check($user, $acos, $requirement['action']);
					}
				else {
					//echo '2.' . $user . ' - ' . $acos . '<br/>';
					$directive = "'$user' on '$acos'";
					$res = $this->Acl->check($user, $acos);
				}
			} else {
				//echo '3.' . $user . ' - ' . $requirement . '<br/>';
				$directive = "'$user' on '$requirement'";
				$res = $this->Acl->check($user, $requirement);
			}

			if ($res === false)
				return array (
					'status' => false,
					'details' => "Authentication failure for $directive"
				);

		} // foreach

		return array (
			'status' => true,
			'details' => 'All requirements ended successfully'
		);
	} // _checkAccessRequirements
	
	function _returnDeniedResult($authResult) {
		if ($authResult['status'] === false) {
			$this->controller->set('denialDetails', $authResult['details']);
			$this->controller->render(null, null, APP . 'plugins/aclite/views/pages/denied.thtml');
			exit ();
		}
	}// _returnAuthResult

} // EasyaclComponent
?>
