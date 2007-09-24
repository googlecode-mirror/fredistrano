<?php

/**
 * PhpgaclPermissions Controller class file.
 *
 * Controller for PhpgaclPermissions actions.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Sypad, Inc.
 * @link			http://www.sypad.com SYPAD
 * @package			sypad
 * @subpackage		sypad.plugins.phpgacl.controllers
 * @since			Sypad v 1.0
 */

/**
 * Actions to manage permissions in phpGACL.
 *
 * @author  Mariano Iglesias
 * @package sypad
 * @subpackage sypad.plugins.phpgacl.controllers
 * @since   1.0
 */
class PhpgaclPermissionsController extends PhpgaclAppController
{
	/**#@+
	 * @access protected
	 */
	/**
	 * Name of the controller.
	 *
	 * @var string
	 * @since 1.0
	 */
	var $name = 'PhpgaclPermissions';
	
	/**
	 * Components used by this controller.
	 *
	 * @var array
	 * @since 1.0
	 */
	var $components = array( 'PhpGacl', 'Session' );
	
	/**
	 * Helpers used by this controller.
	 *
	 * @var array
	 * @since 1.0
	 */
	var $helpers = array( 'Javascript', 'PhpgaclHtml' );
	
	/**
	 * Models used by this controller.
	 *
	 * @var array
	 * @since 1.0
	 */
	var $uses = array('PhpgaclAro', 'PhpgaclAroGroup');
	/**#@-*/
	
	/**
	 * Runs before the action is executed. Used to make sure phpGACL is installed.
	 *
	 * @access protected
	 * @since 1.0
	 */
	function beforeFilter()
	{
		$this->_checkInstalled();
		
		return parent::beforeFilter();
	}
	
	/**
	 * Runs after the action has been executed, and before the view is rendered.
	 *
	 * @access protected
	 * @since 1.0
	 */
	function beforeRender()
	{
		$this->set('title', 'phpGACL :: Access Control');
		
		return parent::beforeRender();
	}
	
	/**
	 * If $value not selected, present the list of groups. If $value selected, run a check on available permissions.
	 *
	 * @param string $value	Group identifier
	 *
	 * @access public
	 * @since 1.0
	 */
	function check($value = null)
	{
		if (isset($value))
		{
			$record = $this->PhpgaclAroGroup->findByValue($value);
			
			if ($record === false || strcasecmp($record['PhpgaclAroGroup']['value'], 'root') == 0)
			{
				$this->redirect($this->pluginUrl . '/phpgaclPermissions/check');
				exit;
			}
			
			// Get ACOs and AXOs
			
			$acos = $this->PhpGacl->getSections('ACO', true);
			$axos = $this->PhpGacl->getAXOs();
			
			if (!is_array($acos) || !is_array($axos) || count($acos) == 0 || count($axos) == 0)
			{
				$this->_missingBasicInformation();
			}
			
			// Only check on ACO sections that have at least one ACO object
			
			foreach($acos as $index => $aco)
			{
				if (!is_array($aco['objects']) || empty($aco['objects']))
				{
					unset($acos[$index]);
				}
			}
			
			// Only check on AXO groups that have at least one AXO section, with at least one AXO object
			
			foreach($axos as $index => $group)
			{
				if (!is_array($group['children']) || empty($group['children']))
				{
					unset($axos[$index]);
				}
				else
				{
					$objects = 0;
					
					foreach($group['children'] as $sectionIndex => $section)
					{
						if (is_array($section['children']) && !empty($section['children']))
						{
							$objects += count($section['children']);
						}
						else
						{
							unset($axos[$index]['children'][$sectionIndex]);
						}
					}
					
					if ($objects == 0)
					{
						unset($axos[$index]);
					}
				}
			}
			
			// Do we have something to check?
			
			if (empty($axos))
			{
				$this->_missingBasicInformation();
			}
			
			// Time consuming task
			
			$this->_avoidTimeout();
			
			// Create test user assigned to selected group
			
			$userIdentifier = 'test.' . uniqid(md5(rand()), true);
			
			$result = $this->PhpGacl->saveUser($userIdentifier, 'phpGACL for CakePHP - Test User');
			
			if (!$result)
			{
				$this->Session->setFlash('Could not create test user to check permissions');
				$this->redirect($this->pluginUrl);
				exit;
			}
			
			$result = $this->PhpGacl->assignGroup($userIdentifier, array ($record['PhpgaclAroGroup']['value']));
			
			if (!$result)
			{
				$this->PhpGacl->delUser($userIdentifier);
				
				$this->Session->setFlash('Could not assign selected group to test user to check permissions');
				$this->redirect($this->pluginUrl);
				exit;
			}
			
			// Do the check
			
			foreach($axos as $index => $group)
			{
					foreach($group['children'] as $sectionIndex => $section)
					{
						foreach($section['children'] as $objectIndex => $object)
						{
							$check = array();
							
							foreach($acos as $acoSectionIndex => $acoSection)
							{
								if (!isset($check[$acoSection['value']]))
								{
									$check[$acoSection['value']] = array();
								}
								
								foreach($acoSection['objects'] as $acoObjectIndex => $acoObject)
								{
									if (!isset($check[$acoSection['value']][$acoObject['value']]))
									{
										$check[$acoSection['value']][$acoObject['value']] = array();
									}
								
									$check[$acoSection['value']][$acoObject['value']] = $this->PhpGacl->checkAcl('user', $userIdentifier, $acoSection['value'], $acoObject['value'], $section['value'], $object['value']);
								}
							}
							
							$axos[$index]['children'][$sectionIndex]['children'][$objectIndex]['check'] = $check;
						}
					}
			}
			
			// Delete dummy user
			
			$result = $this->PhpGacl->delUser($userIdentifier);
			
			if (!$result)
			{
				$this->Session->setFlash('Could not delete temporary user. Remove the user with identifier <strong>' . $userIdentifier . '</strong>');
			}
			
			// Group controllers by plugin
			
			foreach($axos as $index => $element)
			{ 
				if ($element['value'] === 'controller')
				{
					$controllers = $element['children'];
					
					$children = array( ':application:' => array() );
					
					foreach($controllers as $controller)
					{
						if (strpos($controller['name'], $this->PhpGacl->controllerNamePluginSeparator) !== false)
						{
							list($pluginName, $controllerName) = explode($this->PhpGacl->controllerNamePluginSeparator, $controller['name']);
							
							$controller['name'] = $controllerName;
							
							if (!isset($children[$pluginName]))
							{
								$children[$pluginName] = array();
							}
							
							$children[$pluginName][] = $controller;
						}
						else
						{
							$children[':application:'][] = $controller;
						}
					}
					
					$axos[$index]['children'] = $children[':application:'];
					
					unset($children[':application:']);
					
					if (count($children) > 0)
					{
						foreach($children as $plugin => $pluginControllers)
						{
							$element['name'] = $plugin . ' Plugin Controllers';
							$element['children'] = $pluginControllers;
							
							$axos[] = $element;
						}
					}
				}
			}
			
			$this->set('record', $record);
			$this->set('acos', $acos);
			$this->set('elements', $axos);
		}
		else
		{
			$acos = $this->PhpGacl->getSections('ACO', true);
			
			if (!is_array($acos) || count($acos) == 0)
			{
				$this->_missingBasicInformation();
			}
			
			// We don't need the root group
			
			$groups = $this->PhpgaclAroGroup->findAllThreaded(null, null, 'PhpgaclAroGroup.name');
			
			if ($groups === false)
			{
				$groups = array();
			}
			else if (!empty($groups) && isset($groups[0]) && strcasecmp($groups[0]['PhpgaclAroGroup']['value'], 'root') == 0)
			{
				$groups = $groups[0]['children'];
			}
			
			$this->set('groups', $groups);
		}
		
		$this->set('phpgacl_title', (isset($record) ? 'Check permissions for ' . $record['PhpgaclAroGroup']['name'] : 'Choose a Group'));
	}
	
	/**
	 * Edit or add new permission for a group.
	 *
	 * @param string $value	Group identifier.
	 * @param string Group internal identifier (as obtained by _getGroupPermissions())
	 *
	 * @access public
	 * @since 1.0
	 */
	function edit($value, $id = null)
	{
		$record = $this->PhpgaclAroGroup->findByValue($value);
		
		if ($record === false || strcasecmp($record['PhpgaclAroGroup']['value'], 'root') == 0)
		{
			$this->redirect($this->pluginUrl . '/phpgaclPermissions');
			exit;
		}
			
		if (!empty($this->data))
		{
			$acos = array();
			
			if (isset($this->data['Permission']['acos']))
			{
				foreach($this->data['Permission']['acos'] as $aco)
				{
					list($section, $object) = explode('::', $aco);
					
					if (!isset($acos[$section]))
					{
						$acos[$section] = array();
					}
					
					$acos[$section][] = $object;
				}
			}
			
			// Some validation
			
			$save = true;
			
			// Make sure at least an ACO object was selected
			
			if (count($acos) == 0)
			{
				$this->set('form_message', 'You need to select at least one Access Type before proceeding.');
				$save = false;
			}
			
			// If adding, make sure there is no other permission with exact same ACOs
			
			$currentIdentifier = $this->_getAclIdentifier($acos);
			
			$previousPermissions = $this->_getGroupPermissions($record['PhpgaclAroGroup']['value'], $currentIdentifier);
			
			// Get previous permission IDs
				
			if (is_array($previousPermissions) && count($previousPermissions) > 0)
			{
				$previousPermissionIdentifiers = array();
				
				foreach($previousPermissions as $permission)
				{
					$previousPermissionIdentifiers[] = $permission['identifier'];
				}
			}
			
			// Get current permission IDs
			
			if (isset($id))
			{
				$currentPermissions = $this->_getGroupPermissions($record['PhpgaclAroGroup']['value'], $id);
			}
			
			if (isset($currentPermissions) && is_array($currentPermissions))
			{
				$previousPermissionIds = array( 'allow' => array(), 'deny' => array() );
				
				foreach($currentPermissions as $permission)
				{
					$previousPermissionIds[($permission['allow'] ? 'allow' : 'deny')][] = $permission['id'];
				}
			}
			
			// Let's parse the data and set up permissions array (one permission for allowed objects,
			// and one for denied objects)
			
			$permissions = array(
				'allow' => array(),
				'deny' => array()
			);
			
			foreach($this->data['Permission'] as $index => $value)
			{
				if (!is_array($value) && (!empty($value) || $value === '0'))
				{
					$value = intval($value);
					
					list($section, $object) = explode('-', $index);
					
					$type = ($value == 1 ? 'allow' : 'deny');
				
					if (!isset($permissions[$type][$section]))
					{
						$permissions[$type][$section] = array();
					}
					
					$permissions[$type][$section][] = $object;
				}
			}
				
			// Avoid duplicate permissions
			
			if (isset($previousPermissionIdentifiers) && (!isset($id) || !in_array($id, $previousPermissionIdentifiers)))
			{
				$this->set('form_message', 'There is already another permission for this group with the same access type. Try <a href="' . $this->base . '/phpgaclPermissions/edit/' . $record['PhpgaclAroGroup']['value'] . '/' . $currentIdentifier . '" class="important">Editing</a> it instead.');
				$save = false;
			}
			
			// Avoid empty permissions
			
			if (empty($permissions['allow']) && empty($permissions['deny']))
			{
				$this->set('form_message', 'You need to specify at least one non-inherit rule for your permission.');
				$save = false;
			}
			
			if ($save)
			{
				$this->_avoidTimeout();
				
				if (!isset($result) || $result !== false)
				{
					// Save or edit allowed permissions
					
					if (count($permissions['allow']) > 0)
					{
						if (isset($previousPermissionIds) && !empty($previousPermissionIds['allow']))
						{
							$result = $this->PhpGacl->saveAcl($record['PhpgaclAroGroup']['value'], $acos, $permissions['allow'], true, $previousPermissionIds['allow'][0]);
						}
						else
						{
							$result = $this->PhpGacl->saveAcl($record['PhpgaclAroGroup']['value'], $acos, $permissions['allow'], true);
						}
					}
					else if (isset($previousPermissionIds) && !empty($previousPermissionIds['allow']))
					{
						$result = $this->PhpGacl->delPermissions($record['PhpgaclAroGroup']['value'], $previousPermissionIds['allow']);
					}
					
					// Save or edit denied permissions
					
					if (count($permissions['deny']) > 0)
					{
						if (isset($previousPermissionIds) && !empty($previousPermissionIds['deny']))
						{
							$result = $this->PhpGacl->saveAcl($record['PhpgaclAroGroup']['value'], $acos, $permissions['deny'], false, $previousPermissionIds['deny'][0]);
						}
						else
						{
							$result = $this->PhpGacl->saveAcl($record['PhpgaclAroGroup']['value'], $acos, $permissions['deny'], false);
						}
					}
					else if (isset($previousPermissionIds) && !empty($previousPermissionIds['deny']))
					{
						$result = $this->PhpGacl->delPermissions($record['PhpgaclAroGroup']['value'], $previousPermissionIds['deny']);
					}
				}
				
				$this->Session->setFlash(($result ? 'The permission was successfully saved' : 'Could not save permission'));
				$this->redirect($this->pluginUrl . '/phpgaclPermissions/index/' . $record['PhpgaclAroGroup']['value']);
				exit;
			}
			
			$this->data = array();
		}
			
		$acos = $this->PhpGacl->getSections('ACO', true);
		$elements = $this->PhpGacl->getAXOs();
		
		if (!is_array($acos) || !is_array($elements) || count($acos) == 0 || count($elements) == 0)
		{
			$this->_missingBasicInformation();
		}
			
		if (empty($this->data))
		{
			if (isset($id))
			{
				$permissions = $this->_getGroupPermissions($record['PhpgaclAroGroup']['value'], $id);
			}
				
			$selectedAcos = array();
			
			if (isset($permissions) && is_array($permissions) && count($permissions) > 0)
			{
				$this->data = array( 'Permission' => array() );
					
				foreach($permissions as $permission)
				{
					if (isset($permission['type']))
					{
						foreach($permission['type'] as $section => $objects)
						{
							foreach($objects as $object)
							{
								$selectedAcos[$section . '::' . $object] = true;
							}
						}
					}
					
					$this->data['Permission']['acos'] = $selectedAcos;
					
					if (isset($permission['elements']))
					{
						foreach($permission['elements'] as $section => $actions)
						{
							foreach($actions as $action)
							{
								$this->data['Permission'][$section . '-' . $action] = ($permission['allow'] ? 1 : 0);
							}
						}
					}
				}
				
				$selectedAcos = array_keys($selectedAcos);
			}
			
			if (empty($selectedAcos))
			{
				$selectedAcos[] = 'access::execute';
			}
			
			foreach($acos as $index => $section)
			{
				foreach($section['objects'] as $objectIndex => $object)
				{
					$acos[$index]['objects'][$objectIndex]['checked'] = false;
					
					if (in_array($section['value'] . '::' . $object['value'], $selectedAcos))
					{
						$acos[$index]['objects'][$objectIndex]['checked'] = true;
					}
				}
			}
			
			// Group controllers by plugin
			
			foreach($elements as $index => $element)
			{ 
				if ($element['value'] === 'controller')
				{
					$controllers = $element['children'];
					
					$children = array( ':application:' => array() );
					
					foreach($controllers as $controller)
					{
						if (strpos($controller['name'], $this->PhpGacl->controllerNamePluginSeparator) !== false)
						{
							list($pluginName, $controllerName) = explode($this->PhpGacl->controllerNamePluginSeparator, $controller['name']);
							
							$controller['name'] = $controllerName;
							
							if (!isset($children[$pluginName]))
							{
								$children[$pluginName] = array();
							}
							
							$children[$pluginName][] = $controller;
						}
						else
						{
							$children[':application:'][] = $controller;
						}
					}
					
					$elements[$index]['children'] = $children[':application:'];
					
					unset($children[':application:']);
					
					if (count($children) > 0)
					{
						foreach($children as $plugin => $pluginControllers)
						{
							$element['name'] = $plugin . ' Plugin Controllers';
							$element['children'] = $pluginControllers;
							
							$elements[] = $element;
						}
					}
				}
			}
			
			$this->set('record', $record);
			$this->set('elements', $elements);
			$this->set('acos', $acos);
		}
		
		$this->set('phpgacl_title', (isset($id) ? 'Edit' : 'Add') . ' permission for ' . $record['PhpgaclAroGroup']['name']);
	}
	
	/**
	 * Delete permission from group.
	 *
	 * @param string $value	Group identifier.
	 * @param string Group internal identifier (as obtained by _getGroupPermissions())
	 *
	 * @access public
	 * @since 1.0
	 */
	function delete($value, $id)
	{
		$record = $this->PhpgaclAroGroup->findByValue($value);
		
		if ($record === false || strcasecmp($record['PhpgaclAroGroup']['value'], 'root') == 0)
		{
			$this->redirect($this->pluginUrl . '/phpgaclPermissions');
			exit;
		}
		
		$permissions = $this->_getGroupPermissions($record['PhpgaclAroGroup']['value'], $id);
		
		if (!isset($permissions) || !is_array($permissions) || count($permissions) == 0)
		{
			$this->redirect($this->pluginUrl . '/phpgaclPermissions');
			exit;
		}
		
		if (!empty($this->data))
		{
			$ids = array();
			
			foreach($permissions as $permission)
			{
				$ids[] = $permission['id'];
			}
			
			$this->_avoidTimeout();
			
			$result = $this->PhpGacl->delPermissions($record['PhpgaclAroGroup']['value'], $ids);
			
			$this->Session->setFlash(($result ? 'The permission was successfully deleted' : 'Could not delete permission'));
			$this->redirect($this->pluginUrl . '/phpgaclPermissions/index/' . $record['PhpgaclAroGroup']['value']);
			exit;
		}
		
		$this->set('record', $record);
		$this->set('phpgacl_title', 'Delete permission for ' . $record['PhpgaclAroGroup']['name']);
	}
	
	/**
	 * List permissions for a group. If $value not specified, show list of groups.
	 *
	 * @param string $value	Group identifier.
	 *
	 * @access public
	 * @since 1.0
	 */
	function index($value = null)
	{
		if (isset($value))
		{
			$record = $this->PhpgaclAroGroup->findByValue($value);
			
			if ($record === false || strcasecmp($record['PhpgaclAroGroup']['value'], 'root') == 0)
			{
				$this->redirect($this->pluginUrl . '/phpgaclPermissions');
				exit;
			}
			
			$acos = $this->PhpGacl->getSections('ACO', true);
			
			if (!is_array($acos) || count($acos) == 0)
			{
				$this->_missingBasicInformation();
			}
			
			// Rearrange ACOs array so ACO section value is the array index
			
			foreach($acos as $index => $section)
			{
				foreach($section['objects'] as $objectIndex => $object)
				{
					$acos[$index]['objects'][$object['value']] = $object['name'];
					unset($acos[$index]['objects'][$objectIndex]);
				}
				
				$acos[$section['value']] = array (
					'name' => $section['name'],
					'children' => $acos[$index]['objects']
				);
				unset($acos[$index]);
			}
			
			$acls = $this->_getGroupPermissions($record['PhpgaclAroGroup']['value']);
			
			$permissions = array();
			
			// Prepare array of ACLs on a managable format for listing
			
			if (is_array($acls) && count($acls) > 0)
			{
				foreach($acls as $acl)
				{
					$currentIndex = $acl['identifier'];
					
					if (!isset($permissions[$currentIndex]))
					{
						$permissions[$currentIndex] = array();
						
						$permissions[$currentIndex]['identifier'] = $acl['identifier'];
						$permissions[$currentIndex]['sections'] = array();
						$permissions[$currentIndex]['type'] = array();
						
						// Rearrange ACO sections for easy printing
						
						foreach($acl['type'] as $section => $objects)
						{
							$currentTypeIndex = count($permissions[$currentIndex]['type']);
							
							$permissions[$currentIndex]['type'][$currentTypeIndex] = $acos[$section];
							
							foreach($permissions[$currentIndex]['type'][$currentTypeIndex]['children'] as $index => $name)
							{
								if (!in_array($index, $acl['type'][$section]))
								{
									unset($permissions[$currentIndex]['type'][$currentTypeIndex]['children'][$index]);
								}
							}
						}
					}
					
					// Group sections with their objects
					
					foreach($acl['elements'] as $controller => $objects)
					{
						if (!isset($permissions[$currentIndex]['sections'][$controller]))
						{
							$permissions[$currentIndex]['sections'][$controller] = array();
						}
						
						$permissions[$currentIndex]['sections'][$controller] = am($permissions[$currentIndex]['sections'][$controller], $objects);
					}
				}
				
				// Count sections and objects, and reindex to use numeric indexes
				
				foreach($permissions as $currentIndex => $permission)
				{
					$permissions[$currentIndex]['objects'] = 0;
					
					foreach($permission['sections'] as $controller => $objects)
					{
						$permissions[$currentIndex]['objects'] += count($objects);
					}
					
					$permissions[$currentIndex]['sections'] = count($permission['sections']);
					
					$permissions[] = $permissions[$currentIndex];
					unset($permissions[$currentIndex]);
				}
			}
			
			$this->set('record', $record);
			$this->set('permissions', $permissions);
			$this->set('acos', $acos);
		}
		else
		{
			$groups = $this->PhpgaclAroGroup->findAllThreaded(null, null, 'PhpgaclAroGroup.name');
			
			if ($groups === false)
			{
				$groups = array();
			}
			else if (isset($groups[0]) && strcasecmp($groups[0]['PhpgaclAroGroup']['value'], 'root') == 0)
			{
				$groups = $groups[0]['children'];
			}
			
			$this->set('groups', $groups);
		}
		
		$this->set('phpgacl_title', (isset($record) ? 'Set up permissions for ' . $record['PhpgaclAroGroup']['name'] : 'Choose a Group'));
	}
	
	/**
	 * Based on an array of ACOs, get unique identifier.
	 *
	 * @param array $acos	Array of ACOs
	 *
	 * @return string	Unique identifier.
	 *
	 * @access private
	 * @since 1.0
	 */
	function _getAclIdentifier($acos)
	{
		return md5(serialize($acos));
	}
	
	/**
	 * Get permissions assigned to a group.
	 *
	 * @param string $group	Group identifier.
	 * @param string $identifier	Only fetch permissions with this specific identifier (defaults to all)
	 *
	 * @return mixed	Array of permissions, or false if failure.
	 *
	 * @access private
	 * @since 1.0
	 */
	function _getGroupPermissions($group, $identifier = null)
	{
		$this->_avoidTimeout();
		
		$acls = $this->PhpGacl->getPermissions($group, false);
		
		if (is_array($acls) && count($acls) > 0)
		{
			foreach($acls as $index => $acl)
			{
				$acls[$index]['identifier'] = $this->_getAclIdentifier($acl['type']);
				
				if (isset($identifier) && strcmp($identifier, $acls[$index]['identifier']) != 0)
				{
					unset($acls[$index]);
				}
			}
		}
		
		return $acls;
	}
	
	/**
	 * Called when basic information is missing from phpGACL. Sets a flash message
	 * and redirects to home.
	 *
	 * @access private
	 * @since 1.0
	 */
	function _missingBasicInformation()
	{
		$this->Session->setFlash('Could not obtain basic data to assign permissions');
		$this->redirect($this->pluginUrl);
		exit;
	}
}

?>