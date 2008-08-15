<?php
class AcliteComponent extends Object {

	var $name = 'Aclite';

	var $controller = null;

	var $components = array (
		'Acl',
		'Session',
		'RequestHandler'
	);

	function startup(& $controller) {		
		// Is aclite globally disabled (see config)?
		// if ( defined("_ACLITEDISABLED") && _ACLITEDISABLED === true )
		if (Configure::read('Security.aclitedisabled') === true) {
			return ;
		}
			
		$this->controller = & $controller;
		
		// Dynamic aclite   
		$this->Session->delete('dynamicGroup');
		if (method_exists($this->controller, $tmp = 'beforeAuthorize'))
			$this->controller->{$tmp}();
			
		// Init aclite
		$config = & AcliteComponent :: getConfig();

		// Once not twice 
		if ($config['done'] === true) {
			return ;
		}
		
		$authResult = array(
			'status' => true,
			'details' => 'No ACL directive found'
		);
		
		// Check GLOBAL access violation 
		if (isset ($this->controller->authGlobal)) {
			$parentName = get_parent_class($controller);
			$parentName = substr($parentName, 0 , strlen($parentName) - strlen('Controller'));
			$authResult = $this->_checkAccess($parentName,$this->controller->authGlobal);
			
			if ($authResult['status'] === false) {
				return $this->_renderDenial($authResult);
			}
		}
		
		// Check LOCAL access violation 
		if (isset ($this->controller->authLocal)) {
			$authResult = $this->_checkAccess($config['controller'], $this->controller->authLocal);
		
			if ($authResult['status'] === false) {
				return $this->_renderDenial($authResult);
			}
		}

		if ($authResult['status'] === false) {
			return $this->_renderDenial($authResult);
		}
		
		$config['done'] = true;
	} // startup
	
	function & getConfig() {
		static $config = null;
		
		$user = $this->Session->read('User');
		$dynamicGroups = $this->Session->read('dynamicGroup');
		
		if (is_array($dynamicGroups) && is_array($user['Group'])) 
			$groups = array_merge ($user['Group'],$dynamicGroups);
		else if (is_array($dynamicGroups))
			 $groups = $dynamicGroups;
		else if (is_array($user['Group']))
			$groups = $user['Group'];
		else 
			// groupe 'anonymous' si pas de groupe
			$groups[] = array('name' => 'anonymous');
		
		if (!isset ($config) || !$config) {
			$config = array (
				'groups' => $groups, 
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
		 App::import('Model', 'aclite.AclManagement');
		
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
		$groups = $config['groups'];

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
					if (is_array($requirement['action']) && !empty ($requirement['action'])) {
						foreach ($requirement['action'] as $action) {
							$directive = " `$acos` [$action] with group(s) : ";
							foreach ($groups as $group) {
//								echo '3.' . $group . ' - ' . $acos . '<br/>';
								$directive .= $group['name'].' ';
								$res = $this->Acl->check('group.'.$group['name'], $acos,$action);
								if ($res === true)
									continue;
							}	

							if ($res === false)
								return array (
									'status' => false,
									'details' => "Authentication failure for $directive"
								);
						} // foreach
					} else {
						$directive = " `$acos` [".$requirement['action']."] with group(s) : ";
						foreach ($groups as $group) {
//							echo '3.' . $group . ' - ' . $acos . '<br/>';
							$directive .= $group['name'].' ';
							$res = $this->Acl->check('group.'.$group['name'], $acos,$requirement['action']);
							if ($res === true)
								continue;
						}	
					}
				else {
					$directive = " `$acos` with group(s) : ";
					foreach ($groups as $group) {
//						echo '3.' . $group . ' - ' . $acos . '<br/>';
						$directive .= $group['name'].' ';
						$res = $this->Acl->check('group.'.$group['name'], $acos);
						if ($res === true)
							continue;
					}	
				}
			} else {

				$directive = " `$requirement` with group(s) : ";
				foreach ($groups as $group) {
//					echo '3.' . $group . ' - ' . $requirement . '<br/>';
					$directive .= $group['name'].' ';
					$res = $this->Acl->check('group.'.$group['name'], $requirement);
					if ($res === true)
						continue;
				}

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
	
	function _renderDenial($authResult) {
		if ($authResult['status'] === false) {
			$layout = null;
			if ($this->RequestHandler->isAjax()) {
				$layout = 'ajax';
			}
			
			$this->controller->set('denialDetails', $authResult['details']);
			$this->controller->render(null, $layout, APP . 'plugins/aclite/views/pages/denied.ctp');
			return true;
		}
		return false;
	}// _returnAuthResult

} // EasyaclComponent
?>
