<?php

/**
 * PhpGacl Component class file.
 *
 * A wrapper for phpGACL.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Sypad, Inc.
 * @link			http://www.sypad.com SYPAD
 * @package			sypad
 * @subpackage		sypad.controllers.components
 * @since			Sypad v 1.0
 */

define('XMLS_PREFIX_MAXLEN', 15); // ADO DB maximum table prefix

/**
 * Provides a wrapper for phpGACL.
 * 
 * @author		Mariano Iglesias
 * @package		sypad
 * @subpackage	sypad.controllers.components
 */
class PhpGaclComponent extends Object
{
	/**#@+
	 * @access public
	 */
	/**
	 * The prefix used to save controller names as AXO sections.
	 *
	 * @var string
	 * @since 1.0
	 */
	var $sectionControllerPrefix = 'controller.';
	/**
	 * When a controller belongs to a plugin, its name consits of the plugin name, followed by this value, then followed
	 * by the actual controller name.
	 *
	 * @var string
	 * @since 1.0
	 */
	var $controllerNamePluginSeparator = ' Plugin - ';
	/**#@-*/
	/**#@+
	 * @access private
	 */
	/**
	 * Name of this component.
	 *
	 * @var string
	 * @since 1.0
	 */
	var $name = 'PhpGacl';
	/**
	 * Current controller.
	 *
	 * @var mixed
	 * @since 1.0
	 */
	var $controller;
	/**
	 * CakePHP database settings.
	 *
	 * @var array
	 * @since 1.0
	 */
	var $dbSettings;
	/**
	 * phpGacl object.
	 *
	 * @var mixed
	 * @since 1.0
	 */
	var $gacl;
	/**
	 * phpGaclAdmin object (only loaded when needed).
	 *
	 * @var mixed
	 * @since 1.0
	 */
	var $gaclAdmin;
	/**#@-*/
	
	/**
	 * Starts up the component.
	 *
	 * @param mixed	$controller	Controller using the component
	 *
	 * @access public
	 * @since 1.0
	 */
	function startup(&$controller)
	{
		$this->initialize($controller);
	}
	
	/**
	 * Initializes the component, checking if it needs to check ACL access to current controller/action. To do the check
	 * (if necessary) it calls _check()
	 *
	 * @param mixed	$controller	Controller using the component
	 *
	 * @access public
	 * @since 1.0
	 */
	function initialize(&$controller)
	{
		if (!isset($this->controller))
		{
			$this->setController($controller);
		}
		
		if (isset($this->controller->gacl) && (is_array($this->controller->gacl) || (is_bool($this->controller->gacl) && $this->controller->gacl == true)))
		{
			$check = false;
			
			$currentControllerName = $this->controller->name;
			$currentControllerAction = $this->controller->action;
			
			// Check if this action should be protected
			
			if (!isset($this->controller->gacl['check']) || !is_array($this->controller->gacl['check']))
			{
				$check = true;
			}
			else
			{
				$check = false;
				
				$controllers = $this->_parseControllerList($this->controller->gacl['check'], false, false);
				
				if (isset($controllers[$currentControllerName]) && in_array($currentControllerAction, $controllers[$currentControllerName]))
				{
					$check = true;
				}
			}
			
			if ($check)
			{
				$result = $this->_check($currentControllerName, $currentControllerAction);
				
				if ($result == false)
				{
					$redirect = '/';
					
					if (is_array($this->controller->gacl) && isset($this->controller->gacl['denied']) && is_array($this->controller->gacl['denied']) && isset($this->controller->gacl['denied']['type']))
					{
						if (strcasecmp($this->controller->gacl['denied']['type'], 'callback') == 0 && isset($this->controller->gacl['denied']['value']))
						{
							$method = $this->controller->gacl['denied']['value'];
							
							return $this->controller->$method();
						}
						else if (strcasecmp($this->controller->gacl['denied']['type'], 'redirect') == 0 && isset($this->controller->gacl['denied']['value']))
						{
							$redirect = $this->controller->gacl['denied']['value'];
						}
					}
					
					$this->controller->redirect($redirect);
					exit;
				}
			}
		}
	}
	
	/**
	 * Check if the user should be allowed to execute the specified controller, and optionally the specified action.
	 * If $action is not given, it will check if the user has access to at least one action defined for the specified
	 * controller.
	 *
	 * @param mixed $user	If not an array, it will be used as the user identifier, otherwise it will look for an index "id" in the array
	 * @param string $controller	Controller (e.g: 'Posts')
	 * @param string $action	Action (e.g: 'view')
	 *
	 * @return bool	true if access is granted, false otherwise
	 *
	 * @access public
	 * @since 1.0
	 */
	function access($user, $controller, $action = null)
	{
		$this->_initialize();
		
		$result = false;
		
		$controllerValue = $this->sectionControllerPrefix . Inflector::underscore($controller);
		
		if (isset($action))
		{
			$result = $this->checkAcl('user', $user, 'access', 'execute', $controllerValue, $action);
		}
		else
		{
			$this->_initializeAdmin();
			
			// Check for access on ANY action in the controller
			
			$actions = $this->_parseControllerList($controller);
			
			if ($actions !== false && isset($actions[$controllerValue]))
			{
				$actions = $actions[$controllerValue];
				
				for ($i=0, $limiti=count($actions); $result == false && $i < $limiti; $i++)
				{
					$result = $this->checkAcl('user', $user, 'access', 'execute', $controllerValue, $actions[$i]);
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Assign groups to a user. Any groups that are already assigned to the specified user but are not included in $groups will be
	 * unassigned.
	 *
	 * @param mixed $user	If not an array, it will be used as the user identifier, otherwise it will look for an index "id" in the array
	 * @param array $groups	Groups to assign (their identifiers)
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function assignGroup($user, $groups = array())
	{
		$userValue = (is_array($user) && isset($user['id']) ? $user['id'] : strval($user));
		
		return $this->assignGroupObject('user', $userValue, $groups, 'ARO');
	}
	
	/**
	 * Assign groups to an object. Any groups that are already assigned to the specified object but are not included in $groups will be
	 * unassigned.
	 *
	 * @param string $section	Section to which the object belongs to.
	 * @param mixed $object	If not an array, it will be used as the object identifier, otherwise it will look for an index "id" in the array
	 * @param array $groups	Groups to assign (their identifiers)
	 * @param string $type	Object type (ARO or AXO)
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function assignGroupObject($section, $object, $groups = array(), $type = 'ARO')
	{
		$this->_initializeAdmin();
		
		if (!in_array($type, array('ARO', 'AXO')))
		{
			return false;
		}
		
		$objectValue = (is_array($object) && isset($object['id']) ? $object['id'] : strval($object));
		$objectId = $this->gacl_admin->get_object_id($section, $objectValue, $type);
		
		// Remove groups not assigned anymore
		
		$currentGroupIds = $this->gacl_admin->get_object_groups($objectId, $type);
		
		if ($currentGroupIds !== false)
		{
			// Get group data
			
			$currentGroupDatas = array();
			
			foreach($currentGroupIds as $currentGroupId)
			{
				$currentGroupData = $this->_getGroupData($currentGroupId, $type);
				
				if ($currentGroupData !== false)
				{
					$currentGroupDatas[] = $currentGroupData;
				}
			}
			
			// See what groups are no longer there, and which should be added
			
			foreach($groups as $index => $group)
			{
				$groupIndex = -1;
				
				foreach ($currentGroupDatas as $currentGroupDataIndex => $currentGroupData)
				{
					if (strcmp($group, $currentGroupData['show_value']) == 0)
					{
						$groupIndex = $currentGroupDataIndex;
						break;
					}
				}
				
				if ($groupIndex >= 0)
				{
					unset($currentGroupDatas[$groupIndex]);
					unset($groups[$index]);
				}
			}
			
			// Eliminate groups no longer assigned
			
			foreach($currentGroupDatas as $currentGroupData)
			{
				$this->gacl_admin->del_group_object($currentGroupData['id'], $section, $objectValue, $type);
			}
		}
		
		// Assign ARO to ARO groups
		
		if (count($groups) > 0)
		{
			foreach($groups as $group)
			{
				$groupId = $this->gacl_admin->get_group_id($group, null, $type);
				
				if ($groupId !== false)
				{
					$result = $this->gacl_admin->add_group_object($groupId, $section, $objectValue, $type);
					
					if ($result === false)
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Lookup ACL to see if access should be granted.
	 *
	 * @param string $aroSection	ARO section (e.g: 'user')
	 * @param string $aroValue	ARO value (e.g: '1')
	 * @param string $acoSection	ACO section (e.g: 'access')
	 * @param string $acoValue	ACO value (e.g: 'execute')
	 * @param string $axoSection	AXO section (e.g: 'controller.posts')
	 * @param string $axoValue	AXO value (e.g: 'view')
	 *
	 * @return bool	true if access should be granted, false otherwise
	 *
	 * @access public
	 * @since 1.0
	 */
	function checkAcl($aroSection, $aroValue, $acoSection, $acoValue, $axoSection, $axoValue)
	{
		$this->_initialize();
		
		return $this->gacl->acl_check($acoSection, $acoValue, $aroSection, $aroValue, $axoSection, $axoValue);
	}
	
	/**
	 * Delete a controller.
	 *
	 * @param string $controller	Controller to delete (e.g: Posts)
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function delController($controller)
	{
		$controllerValue = $this->sectionControllerPrefix . Inflector::underscore($controller);
		
		return $this->delSection($controllerValue, 'AXO', true);
	}
	
	/**
	 * Delete a group and optionally its children (or set them to belong to the root group)
	 *
	 * @param mixed $group	If not an array, it will be used as the identifier value, otherwise it will look for an index "id" in the array
	 * @param bool $reparent	If true, children of this group will become child of this group's parent, otherwise they'll be deleted.
	 * @param string $type	Group type (ARO or AXO)
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function delGroup($group, $reparent = true, $type = 'ARO')
	{
		$this->_initializeAdmin();
		
		$groupValue = (is_array($group) && isset($group['id']) ? $group['id'] : strval($group));
		
		if (!in_array($type, array('ARO', 'AXO')) || strcasecmp($groupValue, 'root') == 0)
		{
			return false;
		}
		
		$result = false;
		
		$groupId = $this->gacl_admin->get_group_id($groupValue, null, $type);
		
		if ($groupId !== false)
		{
			$result = $this->gacl_admin->del_group($groupId, $reparent, $type);
			
			if ($result !== false && isset($this->_data) && isset($this->_data['group']) && isset($this->_data['group'][$groupId]))
			{
				unset($this->_data['group'][$groupId]);
			}
		}
		
		return $result;
	}
	
	/**
	 * Delete an ACO/ARO/AXO object.
	 *
	 * @param string $section	Section identifier (e.g: access)
	 * @param string $value	Object identifier (e.g: execute)
	 * @param string $type	Type of section (valid values: ACO, ARO, AXO; defaults to ACO)
	 * @param bool $recursive	Remove referencing objects if true, leave them alone otherwise.
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function delObject($section, $value, $type = 'ACO', $recursive = true)
	{
		$this->_initializeAdmin();
		
		if (!in_array($type, array ('ACO', 'ARO', 'AXO')))
		{
			return false;
		}
		
		$result = false;
		
		$objectId = $this->gacl_admin->get_object_id($section, $value, $type);
		
		if ($objectId !== false)
		{
			$result = $this->gacl_admin->del_object($objectId, $type, $recursive);
		}
		
		return ($result !== false);
	}
	
	/**
	 * Delete permissions associated to a group.
	 *
	 * @param mixed $group	If not an array, it will be used as the identifier value, otherwise it will look for an index "id" in the array
	 * @param array $ids	Only delete these specific ACLs
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function delPermissions($group, $ids = null)
	{
		$this->_initializeAdmin();
		
		$result = false;
		
		$groupValue = (is_array($group) && isset($group['id']) ? $group['id'] : strval($group));
		$groupId = $this->gacl_admin->get_group_id($groupValue, null, 'ARO');
		
		if ($groupId !== false)
		{
			$groupData = $this->_getGroupData($groupId, 'ARO');
			
			if (isset($ids))
			{
				$aclIds = $ids;
			}
			else
			{
				$aclIds = $this->gacl_admin->search_acl(false, false, false, false, $groupData['name'], false, false, false);
			}
			
			if ($aclIds !== false)
			{
				$result = true;
				
				foreach($aclIds as $aclId)
				{
					$result = ($this->gacl_admin->del_acl($aclId) && $result);
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Delete an ACO/ARO/AXO section.
	 *
	 * @param string $section	Section identifier (e.g: access)
	 * @param string $type	Type of section (valid values: ACO, ARO, AXO; defaults to ACO)
	 * @param bool $recursive	Remove referencing objects if true, leave them alone otherwise.
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function delSection($section, $type = 'ACO', $recursive = true)
	{
		$this->_initializeAdmin();
		
		if (!in_array($type, array ('ACO', 'ARO', 'AXO')))
		{
			return false;
		}
		
		$result = false;
		
		$sectionId = $this->gacl_admin->get_object_section_section_id(null, $section, $type);
		
		if ($sectionId !== false)
		{
			$result = $this->gacl_admin->del_object_section($sectionId, $type, $recursive);
		}
		
		return ($result !== false);
	}
	
	/**
	 * Delete a user.
	 *
	 * @param mixed $user	If not an array, it will be used as the user identifier, otherwise it will look for an index "id" in the array
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function delUser($user)
	{
		$this->_initializeAdmin();
		
		$userValue = (is_array($user) && isset($user['id']) ? $user['id'] : strval($user));
		
		$userId = $this->gacl_admin->get_object_id('user', $userValue, 'ARO');
		
		if ($userId !== false)
		{
			return $this->gacl_admin->del_object($userId, 'ARO', true);
		}
		
		return false;
	}
	
	/**
	 * Returns all AXO protectable elements grouped by their AXO group, and AXO section. It returns an array, for each AXO group, of the form:
	 * array (
	 *	'id' => 'group_id',
	 *	'name' => 'group_name',
	 *	'children' => array (
	 *		[multiple] => array (
	 *			'value' => 'section_value',
	 *			'name' => 'section_name',
	 *			'children' => array(
	 *				[multiple] => array(
	 *					'value' => 'object_value',
	 *					'name' => 'object_name'
	 *				)
	 *			)
	 *		)
	 *	)
	 * )
	 *
	 * @return array	Array of AXO elements grouped by their AXO group, and AXO section
	 *
	 * @access public
	 * @since 1.0
	 */
	function getAXOs()
	{
		$axoGroups = $this->getGroups('AXO', true);
		
		if (is_array($axoGroups) && count($axoGroups) > 0)
		{
			$axoGroupIndexes = array();
			
			foreach($axoGroups as $index => $group)
			{
				$axoGroupIndexes[$group['id']] = $index;
				
				$axoGroups[$index]['children'] = array();
			}
			
			$axoSections = $this->getSections('AXO', true);
			
			if (is_array($axoSections) && count($axoSections) > 0)
			{
				foreach($axoSections as $index => $section)
				{
					if (isset($section['objects']) && is_array($section['objects']))
					{
						foreach($section['objects'] as $objectIndex => $object)
						{
							$objectGroups = $this->getObjectGroups($section['value'], $object['value'], 'AXO', true);
							
							if (is_array($objectGroups))
							{
								foreach($objectGroups as $objectGroup)
								{
									if (!isset($axoGroups[$axoGroupIndexes[$objectGroup]]['children'][$section['value']]))
									{
										$axoGroups[$axoGroupIndexes[$objectGroup]]['children'][$section['value']] = array(
											'value' => $section['value'],
											'name' => $section['name'],
											'children' => array()
										);
									}
									
									$axoGroups[$axoGroupIndexes[$objectGroup]]['children'][$section['value']]['children'][] = $object;
								}
							}
						}
					}
				}
			}
			
			foreach($axoGroups as $index => $group)
			{
				foreach($group['children'] as $elementIndex => $element)
				{
					$axoGroups[$index]['children'][] = $element;
					
					unset($axoGroups[$index]['children'][$elementIndex]);
				}
			}
		}
		
		return $axoGroups;
	}
	
	/**
	 * Get all available controllers defined in GACL or the application. Returns an array of indexed elements, where each
	 * element is of the form ( 'value' => the section value for the controller, 'name' => the descriptive name ). If $real
	 * is set to true it will also give the 'path' for each controller.
	 *
	 * @param bool $real	If set to true, get all controllers defined in the CakePHP application instead (defaults to false)
	 *
	 * @return array	The array of controllers, false if failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function getControllers($real = false)
	{
		$controllers = false;
		
		if ($real)
		{
			$controllerPaths = array (
				CONTROLLERS
			);
			
			$controllerPaths = am($controllerPaths, $this->_getPluginControllerPaths());
		
			$controllerPathFiles = array();
			
			foreach($controllerPaths as $controllerPath)
			{
				if ($controllerPath[strlen($controllerPath) - 1] == DS)
				{
					$controllerPath = substr($controllerPath, 0, strlen($controllerPath) - 1);
				}
					
				$controllerPathFiles[$controllerPath] = listClasses($controllerPath);
			}
			
			$controllers = array();
			
			foreach($controllerPathFiles as $controllerPath => $controllerFiles)
			{
				foreach($controllerFiles as $controllerFile)
				{
					list($controllerName) = explode('.', $controllerFile);
					
					$controllerName = Inflector::camelize(substr($controllerName, 0, strrpos($controllerName, '_controller')));
					
					$controllerValue = $this->sectionControllerPrefix . Inflector::underscore($controllerName);
					
					$controllers[] = array(
						'value' => $controllerValue,
						'name' => $controllerName,
						'path' => $controllerPath
					);
				}
			}
		}
		else
		{
			$this->_initializeAdmin();
			
			$sections = $this->getSections('AXO');
			
			if ($sections !== false)
			{
				$controllers = array();
				
				foreach($sections as $section)
				{
					if (strpos($section['value'], $this->sectionControllerPrefix) === 0)
					{
						$controllers[] = $section;
					}
				}
			}
		}
		
		if ($controllers !== false)
		{
			// Sort by name
			
			$name = array();
			
			foreach($controllers as $index => $controller)
			{
				$name[$index] = $controller['name'];
			}
			
			array_multisort($name, SORT_ASC, $controllers);
		}
		
		return $controllers;
	}
	
	/**
	 * Get all actions defined for the specified controller in GACL. Returns an array of indexed elements, where each
	 * element is of the form ( 'value' => the object value for the action, 'name' => the descriptive name ).
	 * If $real is set to true it will instead return an array of actions defined in the controller.
	 *
	 * @param string $controllerName	The controller name (e.g: Posts)
	 * @param bool $real	If set to true, get actions defined in the controller class instead (defaults to false)
	 * @param string $controllerPath	Path where the controller can be found (defaults to CONTROLLERS)
	 *
	 * @return array	The array of actions, false if failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function getControllerActions($controllerName, $real = false, $controllerPath = CONTROLLERS)
	{
		if (strpos($controllerName, $this->controllerNamePluginSeparator) !== false)
		{
			list($pluginName, $controllerName) = explode($this->controllerNamePluginSeparator, $controllerName);
		}
			
		if ($real)
		{
			$result = $this->_getControllerActions($controllerName, $controllerPath);
			
			$actions = array();
			
			if (is_array($result))
			{
				foreach($result as $action)
				{
					$actions[] = array (
						'value' => $action,
						'name' => $action . '()'
					);
				}
			}
		}
		else
		{
			$this->_initializeAdmin();
			
			$controllerValue = $this->sectionControllerPrefix . Inflector::underscore($controllerName);
			
			$actions = $this->getObjects($controllerValue, 'AXO');
		}
		
		if ($actions !== false)
		{
			// Sort by name
			
			$name = array();
			
			foreach($actions as $index => $action)
			{
				$name[$index] = $action['name'];
			}
			
			array_multisort($name, SORT_ASC, $actions);
		}
		
		return $actions;
	}
	
	/**
	 * Gets CakePHP database settings as an indexed array.
	 *
	 * @param string $connection	Fetch the specified connection (defaults to GACL default connection)
	 *
	 * @return array	Database settings (indexes are: db_type, db_host, db_user, db_password, db_name, and db_table_prefix)
	 *
	 * @access private
	 * @since 1.0
	 */
	function getDatabaseSettings($connection = null)
	{
		if (isset($connection) || !isset($this->dbSettings))
		{
			uses('model' . DS . 'connection_manager');
			
			// Get database configuration as defined in CakePHP
			
			$connectionManager = ConnectionManager::getInstance();
			
			if (isset($connection))
			{
				$connectionName = $connection;
			}
			else
			{
				$connectionName = (isset($connectionManager->config->gacl) ? 'gacl' : 'default');
			}
			
			$databaseConfig = $connectionManager->config->$connectionName;
			
			// Set up the phpGacl object
			
			$dbSettings = array(
				'db_type' => $databaseConfig['driver'],
				'db_host' => $databaseConfig['host'],
				'db_user' => $databaseConfig['login'],
				'db_password' => $databaseConfig['password'],
				'db_name' => $databaseConfig['database'],
				'db_table_prefix' => $databaseConfig['prefix'],
			);
			
			if (!isset($connection))
			{
				$this->dbSettings = $dbSettings;
			}
			else
			{
				return $dbSettings;
			}
		}
		
		return $this->dbSettings;
	}
	
	/**
	 * Get the available groups as an indexed threaded array.
	 *
	 * @param string $type	Group type (ARO or AXO)
	 *
	 * @return array	An indexed array in the form of group_identifier => ('id' => group_id, 'name'=>group_name, 'children'=> indexed child array)
	 *
	 * @access public
	 * @since 1.0
	 */
	function getGroups($type = 'ARO', $flat = false)
	{
		$this->_initializeAdmin();
		
		if (!in_array($type, array('ARO', 'AXO')))
		{
			return false;
		}
		
		if ($flat)
		{
			$groupIds = $this->gacl_admin->get_group_children($this->gacl_admin->get_root_group_id($type), $type, 'RECURSE');
			
			if ($groupIds === false)
			{
				return false;
			}
			else if (count($groupIds) == 0)
			{
				return array();
			}
			
			$groups = array();
			
			foreach($groupIds as $groupId)
			{
				$groupData = $this->_getGroupData($groupId, $type);
				
				$groups[] = $groupData;
			}
			
			return $groups;
		}
		else
		{
			$groups = $this->gacl_admin->sort_groups($type);
		}
		
		foreach($groups as $id => $elements)
		{
			$groups[$id] = array(
				'children' => $elements
			);
		}
		
		$groupDatas = array();
		
		// Get data for each group
		
		foreach($groups as $id => $group)
		{
			if ($id > 0)
			{
				$groups[$id] = $this->_getGroupData($id, $type);
			}
			
			// Get data for its children
			
			foreach($group['children'] as $childId => $childGroup)
			{
				$groups[$id]['children'][$childId] = $this->_getGroupData($childId, $type);
			}
		}
		
		// Get the root ID
		
		$rootGroupId = $this->gacl_admin->get_root_group_id($type);
		
		// Get it as a threaded array, starting from the root
		
		if (count($groups) > 1)
		{
			$groups = $this->_getGroupThread($groups[$rootGroupId], $groups);
			
			// We don't need the root
			
			$groups = $groups['children'];
		}
		else
		{
			// We have only root group, so no real groups to show
			
			$groups = array();
		}
		
		return $groups;
	}
	
	/**
	 * Get the groups assigned to an object.
	 *
	 * @param string $section	Section to which the object belongs (example: 'user')
	 * @param mixed $object	If not an array, it will be used as the object identifier, otherwise it will look for an index "id" in the array
	 * @param string $type	Object type (ARO or AXO)
	 * @param bool $onlyId	Only get internal group IDs (defaults to false)
	 *
	 * @return mixed	An array with the group identifiers assigned to the object, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function getObjectGroups($section, $object, $type = 'ARO', $onlyId = false)
	{
		$this->_initializeAdmin();
		
		if (!in_array($type, array('ARO', 'AXO')))
		{
			return false;
		}
		
		$objectValue = (is_array($object) && isset($object['id']) ? $object['id'] : strval($object));
		$objectId = $this->gacl_admin->get_object_id($section, $objectValue, $type);
		
		$result = false;
		
		$currentGroupIds = $this->gacl_admin->get_object_groups($objectId, $type);
		
		if ($onlyId !== true && $currentGroupIds !== false)
		{
			$result = array();
			
			foreach($currentGroupIds as $currentGroupId)
			{
				$currentGroupData = $this->_getGroupData($currentGroupId, $type);
				
				if ($currentGroupData !== false)
				{
					$result[] = $currentGroupData['show_value'];
				}
			}
		}
		else if ($currentGroupIds !== false)
		{
			$result = $currentGroupIds;
		}
		
		return $result;
	}
	
	/**
	 * Get ACO/ARO/AXO objects for a section as an array where each element is of the form 'value' => value of the object, 'name' => name.
	 *
	 * @param string $section	Section to which the object belongs to
	 * @param string $type	Type of section (valid values: ACO, ARO, AXO; defaults to ACO)
	 *
	 * @return array	Available objects
	 *
	 * @access public
	 * @since 1.0
	 */
	function getObjects($section, $type = 'ACO')
	{
		$this->_initializeAdmin();
		
		if (!in_array($type, array ('ACO', 'ARO', 'AXO')))
		{
			return false;
		}
		
		$result = $this->gacl_admin->get_object($section, 0, $type);
		
		if ($result !== false)
		{
			$objects = array();
			
			foreach($result as $id)
			{
				$object = $this->gacl_admin->get_object_data($id, $type);
				
				if ($object !== false)
				{
					$object = array(
						'value' => $object[0][1],
						'name' => $object[0][3]
					);
				}
				
				$objects[] = $object;
			}
		}
		else
		{
			$objects = false;
		}
	
		return $objects;
	}
	
	/**
	 * Get permissions associated to a group. The result (on success) will be an array of permissions, each
	 * permission being an associative array containing the indexes: 
	 * - id: id of the permission (useful for editing)
	 * - allow: boolean value, indicating if permission is allowed (true) or denied (false)
	 * - type: an associative array of ACO sections (access category) => array of ACO objects (access types)
	 * - elements: an associative array of controllers => array of actions
	 *
	 * @param mixed $group	If not an array, it will be used as the identifier value, otherwise it will look for an index "id" in the array
	 * @param bool $cahngeControllerValues	Set to true if those AXO sections that are controllers should be named as CakePHP controllers (defaults to true)
	 *
	 * @return array	Array of permissions, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function getPermissions($group, $cahngeControllerValues = true)
	{
		$this->_initializeAdmin();
		
		$groupValue = (is_array($group) && isset($group['id']) ? $group['id'] : strval($group));
		$groupId = $this->gacl_admin->get_group_id($groupValue, null, 'ARO');
		
		if ($groupId === false)
		{
			return false;
		}
		
		$groupData = $this->_getGroupData($groupId, 'ARO');
		
		$aclIds = $this->gacl_admin->search_acl(false, false, false, false, $groupData['name'], false, false, false);
		
		$permissions = array();
		
		if ($aclIds !== false)
		{
			foreach($aclIds as $aclId)
			{
				$acl = $this->gacl_admin->get_acl($aclId);
				
				$permission = array(
					'id' => $aclId,
					'allow' => $acl['allow'] == 1 ? true : false,
					'type' => $acl['aco'],
					'elements' => $acl['axo']
				);
				
				foreach($permission['elements'] as $axoSectionValue => $axoElements)
				{
					if (strpos($axoSectionValue, $this->sectionControllerPrefix) === 0)
					{
						unset($permission['elements'][$axoSectionValue]);
					
						if ($cahngeControllerValues)
						{
							$axoSectionValue = Inflector::camelize(str_replace($this->sectionControllerPrefix, '', $axoSectionValue));
						}
						
						$permission['elements'][$axoSectionValue] = $axoElements;
					}
				}
				
				$permissions[] = $permission;
			}
		}
		
		return $permissions;
	}
	
	/**
	 * Get ACO/ARO/AXO sections as an array where each element is of the form 'value' => value of the section, 'name' => name.
	 * If $includeObjects is set to true, it will also include all objects belonging to this section on an index called 'objects'
	 *
	 * @param string $type	Type of section (valid values: ACO, ARO, AXO; defaults to ACO)
	 * @param bool $includeObjects	Also include assigned objects for each section.
	 *
	 * @return array	Available sections
	 *
	 * @access public
	 * @since 1.0
	 */
	function getSections($type = 'ACO', $includeObjects = false)
	{
		$this->_initializeAdmin();
		
		if (!in_array($type, array ('ACO', 'ARO', 'AXO')))
		{
			return false;
		}
		
		// No other way to get all available sections :(
		
		$query = 'SELECT value, name FROM '. $this->gacl_admin->_db_table_prefix . strtolower($type) .'_sections WHERE hidden=0 ORDER BY order_value, name';
		
		$resultSet = $this->gacl_admin->db->Execute($query);
		
		if (is_object($resultSet))
		{
			$sections = array();
			
			while ($row = $resultSet->FetchRow())
			{
				$section = array (
					'value' => $row[0],
					'name' => $row[1]
				);
				
				$sections[] = $section;
			}
			
			if ($includeObjects)
			{
				foreach($sections as $index => $section)
				{
					$sections[$index]['objects'] = $this->getObjects($section['value'], $type);
				}
			}
		}
		else
		{
			$sections = false;
		}
		
		return $sections;
	}
	
	/**
	 * Get the groups assigned to a user.
	 *
	 * @param mixed $user	If not an array, it will be used as the user identifier, otherwise it will look for an index "id" in the array
	 *
	 * @return mixed	An array with the group identifiers assigned to the user, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function getUserGroups($user)
	{
		$this->_initializeAdmin();
		
		$userValue = (is_array($user) && isset($user['id']) ? $user['id'] : strval($user));
		$userId = $this->gacl_admin->get_object_id('user', $userValue, 'ARO');
		
		$result = false;
		
		$currentGroupIds = $this->gacl_admin->get_object_groups($userId, 'ARO');
		
		if ($currentGroupIds !== false)
		{
			$result = array();
			
			foreach($currentGroupIds as $currentGroupId)
			{
				$currentGroupData = $this->_getGroupData($currentGroupId, 'ARO');
				
				if ($currentGroupData !== false)
				{
					$result[] = $currentGroupData['show_value'];
				}
			}
		}
		
		return $this->getObjectGroups('user', $userValue, 'ARO');
	}
	
	/**
	 * Imports all controllers and its actions into GACL.
	 *
	 * @param string $path	Only look for controllers here (defaults to null, which makes it look wherever there could be a controller)
	 *
	 * @return bool	true on success, false on failure.
	 *
	 * @access public
	 * @since 1.0
	 */
	function importControllers($path = null)
	{
		$this->_initializeAdmin();
		
		if (isset($path))
		{
			$controllerPaths = array( $path . ($path[strlen($path) - 1] != DS ? DS : '') );
		}
		else
		{
			$controllerPaths = array (
				CONTROLLERS
			);
			
			$controllerPaths = am($controllerPaths, $this->_getPluginControllerPaths());
		}
	
		$controllerFiles = array();
		
		foreach($controllerPaths as $controllerPath)
		{
			$controllerFiles[$controllerPath] = listClasses($controllerPath);
		}
		
		if (count($controllerFiles) > 0)
		{
			foreach($controllerFiles as $controllerPath => $controllerFiles)
			{
				foreach($controllerFiles as $controllerFile)
				{
					$result = $this->_installDataController($controllerFile, null, $controllerPath);
					
					if ($result === false)
					{
						return false;
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Install phpGACL data if it has not been already installed.
	 *
	 * @return bool	true if installation done (or already installed), false otherwise.
	 *
	 * @access public
	 * @since 1.0
	 */
	function install()
	{
		if (!$this->isInstalled())
		{
			$settings = $this->getDatabaseSettings();
			
			return $this->_install($settings);
		}
		
		return true;
	}
	
	/**
	 * Tell if phpGACL is installed.
	 *
	 * @return bool	true if phpGACL data is installed, false otherwise.
	 *
	 * @access public
	 * @since 1.0
	 */
	function isInstalled()
	{
		$this->_initialize();
		
		if (!isset($this->gacl))
		{
			return false;
		}
		
		$result = ($this->gacl->acl_get_groups('dummy', 'dummy') !== false);
		
		return $result;
	}
	
	/**
	 * Adds a permission to a group. You can specify $axoArray as just one AXO section (e.g: 'Posts')
	 * on which case all AXO objects for that section will be included; as an array with more than one
	 * AXO sections; or as an array where elements are of the form AXO section => AXO objects, where objects
	 * is itself an array.
	 *
	 * Example valid values for $axoArray:
	 *
	 * 'controller.posts': allow access to all actions in controller Posts.
	 * array ('controller.posts', 'controller.users'): allow access to all actions in controllers Posts and Users.
	 * array ('controller.posts', 'controller.users' => 'view'): allow access to all actions in controller Posts, and only action
	 * 																		 view in controller Users.
	 * array ('controller.posts', 'controller.users' => array('index', 'view')): allow access to all actions in controller Posts,
	 *																		 and actions index and view in controller Users.
	 *
	 * @param string $group	Group identifier.
	 * @param array $acos	Associative array in the form of [ACO section] => array of [ACO Objects]. Eg: 'access' => array('execute')
	 * @param array $axoArray	Associative array in the form of [AXO section] => array of [AXO objects]. Eg: 'controller.posts' => array('index')
	 * @param bool $allow	Type of permission, true to allow, false to deny (defaults to true)
	 * @param int $id	ID of the permission (if editing), defaults to null
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function saveAcl($group, $acos, $axoArray, $allow = true, $id = null)
	{
		$this->_initializeAdmin();
		
		$groupValue = (is_array($group) && isset($group['id']) ? $group['id'] : strval($group));
		$groupId = $this->gacl_admin->get_group_id($groupValue, null, 'ARO');
		
		if ($groupId === false)
		{
			return false;
		}
		
		$result = false;
		
		$aroGroups = array ( $groupId );
		$permission = ($allow === false ? 0 : 1);
		$aclSectionValue = 'system';
		
		if (isset($id) && $id > 0)
		{
			$result = @$this->gacl_admin->edit_acl($id, $acos, null, $aroGroups, $axoArray, null, $permission, 1, null, null, $aclSectionValue, false);
		}
		else
		{
			$result = @$this->gacl_admin->add_acl($acos, null, $aroGroups, $axoArray, null, $permission, 1, null, null, $aclSectionValue, false);
		}
		
		$result = ($result !== false);
		
		return $result;
	}
	
	/**
	 * Add a controller to the GACL system. It will add the controller (if it hasn't been
	 * already added) and its actions (whichever actions were not previously added).
	 *
	 * @param string $controllerName	The controller name (e.g: Posts)
	 * @param array $actions	Only add this specific set of actions (defaults to all actions defined in controller)
	 * @param string $controllerPath	Path where the controller can be found (defaults to CONTROLLERS)
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function saveController($controllerName, $actions = null, $controllerPath = CONTROLLERS)
	{
		$controllerFile = Inflector::underscore($controllerName) . '_controller.php';
		
		if ($controllerPath[strlen($controllerPath) - 1] != DS)
		{
			$controllerPath .= DS;
		}
		
		if (is_readable($controllerPath . $controllerFile))
		{
			return $this->_installDataController($controllerFile, $actions, $controllerPath);
		}
		
		return false;
	}
	
	/**
	 * Edit or Add an ARO/AXO group.
	 *
	 * @param mixed $group	If not an array, it will be used as the identifier value, otherwise it will look for an index "id" in the array
	 * @param string $name	The descriptive name for the group (must be unique)
	 * @param mixed $parent	Set null for root (defaults to root). Identifier of its parent. If not an array, it will be used as the identifier value, otherwise it will look for an index "id" in the array
	 * @param string $type	Group type (ARO or AXO)
	 * @param int $groupId	Set to group's inernal ID (defaults to null, which makes it look the ID by its $group value)
	 * @param int $groupParentId	Set to group's inernal ID (defaults to null, which makes it look the ID by its $parent value)
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function saveGroup($group, $name = null, $parent = null, $type = 'ARO', $groupId = null, $groupParentId = null)
	{
		$this->_initializeAdmin();
		
		if (!in_array($type, array('ARO', 'AXO')))
		{
			return false;
		}
		
		$groupValue = (is_array($group) && isset($group['id']) ? $group['id'] : strval($group));
		$groupName = (isset($name) ? $name : (is_array($group) && isset($group['name']) ? $group['name'] : ucwords($groupValue)) );
		
		// Make sure the group name is unique
		
		$groupIds = $this->gacl_admin->get_group_children($this->gacl_admin->get_root_group_id($type), $type, 'RECURSE');
		
		if ($groupIds !== false)
		{
			$groups = array();
			$groupNames = array();
			
			foreach($groupIds as $currentGroupId)
			{
				$groups[$currentGroupId] = $this->_getGroupData($currentGroupId, $type);
				
				if (strcmp($groupValue, $groups[$currentGroupId]['value']) != 0)
				{
					$groupNames[] = $groups[$currentGroupId]['name'];
				}
			}
			
			// Look for same name
			
			$collision = false;
			
			foreach($groups as $id => $group)
			{
				if (strcasecmp($groupName, $group['name']) == 0 && strcmp($groupValue, $group['value']) != 0)
				{
					$collision = true;
				}
			}
			
			// Set up a unique name
			
			if ($collision)
			{
				$collisionIndex = 1;
				
				while (in_array($groupName . ' (' . $collisionIndex . ')', $groupNames))
				{
					$collisionIndex++;
				}
				
				$groupName .= ' (' . $collisionIndex . ')';
			}
		}
		
		if (empty($groupValue))
		{
			return false;
		}
		
		$result = false;
		
		// Set up group parent
		
		if (!empty($parent))
		{
			$parent = (is_array($parent) && isset($parent['id']) ? $parent['id'] : strval($parent));
		}
		
		if (!isset($groupParentId))
		{
			if (!empty($parent))
			{
				$groupParentId = $this->gacl_admin->get_group_id($parent, null, $type);
			}
			
			if (empty($parent) || $groupParentId === false)
			{
				$groupParentId = $this->gacl_admin->get_root_group_id($type);
			}
		}
		
		// Save group (add or edit)
		
		if (!isset($groupId))
		{
			$groupId = $this->gacl_admin->get_group_id($groupValue, null, $type);
		}
		
		$result = false;
		
		if ($groupId === false)
		{
			$result = $this->gacl_admin->add_group($groupValue, $groupName, $groupParentId, $type);
		}
		else
		{
			$result = $this->gacl_admin->edit_group($groupId, $groupValue, $groupName, $groupParentId, $type);
		}
		
		$result = ($result !== false);
		
		if ($groupId !== false && $result !== false && isset($this->_data) && isset($this->_data['group']) && isset($this->_data['group'][$groupId]))
		{
			unset($this->_data['group'][$groupId]);
		}
		
		return $result;
	}
	
	/**
	 * Add or edit an ACO/ARO/AXO object to a section.
	 *
	 * @param string $section	Section identifier (e.g: access)
	 * @param string $value	Object identifier (e.g: execute)
	 * @param string $name	Descriptive name for the section (e.g: Execute)
	 * @param string $type	Type of section (valid values: ACO, ARO, AXO; defaults to ACO)
	 * @param int $objectId	Edit this object (ID is phpGACL's internal id, otherwise use $value to find object)
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function saveObject($section, $value, $name, $type = 'ACO', $objectId = null)
	{
		$this->_initializeAdmin();
		
		if (!in_array($type, array ('ACO', 'ARO', 'AXO')))
		{
			return false;
		}
		
		if (!isset($objectId))
		{
			$objectId = $this->gacl_admin->get_object_id($section, $value, $type);
		}
		
		$result = false;
		
		if ($objectId !== false)
		{
			$result = $this->gacl_admin->edit_object($objectId, $section, $name, $value, 0, 0, $type);
		}
		else
		{
			$result = $this->gacl_admin->add_object($section, $name, $value, 0, 0, $type);
		}
		
		return ($result !== false);
	}
	
	/**
	 * Saves a permission for a controller to a group. You can specify $controllers as just one controller (e.g: 'Posts')
	 * on which case all actions for that controller will be included; as an array with more than one
	 * controller; or as an array where controllers are of the form controller => actions, where actions
	 * is itself an array.
	 *
	 * Example valid values for $controllers:
	 *
	 * 'Posts': allow access to all actions in controller Posts.
	 * array ('Posts', 'Users'): allow access to all actions in controllers Posts and Users.
	 * array ('Posts', 'Users' => 'view'): allow access to all actions in controller Posts, and only action
	 * 																		 view in controller Users.
	 * array ('Posts', 'Users' => array('index', 'view')): allow access to all actions in controller Posts,
	 *																		 and actions index and view in controller Users.
	 *
	 * @param string $group	Group identifier.
	 * @param mixed $controllers	A string identifying controller, or array of controllers/actions.
	 * @param bool $allow	Type of permission, true to allow, false to deny (defaults to true)
	 * @param int $id	ID of the permission (if editing), defaults to null
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function savePermission($group, $controllers, $allow = true, $id = null)
	{
		$this->_initializeAdmin();
		
		return $this->saveAcl($group, array ('access' => array('execute')), $this->_parseControllerList($controllers), $allow, $id);
	}
	
	/**
	 * Add or edit an ACO/ARO/AXO section.
	 *
	 * @param string $section	Section identifier (e.g: model)
	 * @param string $name	Descriptive name for the section (e.g: Models)
	 * @param string $type	Type of section (valid values: ACO, ARO, AXO; defaults to ACO)
	 * @param int $sectionId	Edit this specific section (ID is phpGACL's ID for the section, defaults to empty, which means it should look based on its $section value)
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function saveSection($section, $name, $type = 'ACO', $sectionId = null)
	{
		$this->_initializeAdmin();
		
		if (!in_array($type, array ('ACO', 'ARO', 'AXO')))
		{
			return false;
		}
		
		if (!isset($sectionId))
		{
			$sectionId = $this->gacl_admin->get_object_section_section_id(null, $section, $type);
		}
		
		$result = false;
		
		if ($sectionId !== false)
		{
			$result = $this->gacl_admin->edit_object_section($sectionId, $name, $section, 0, 0, $type);
		}
		else
		{
			$result = $this->gacl_admin->add_object_section($name, $section, 0, 0, $type);
		}
		
		return ($result !== false);
	}
	
	/**
	 * Edit or Add a user.
	 *
	 * @param mixed $user	If not an array, it will be used as the user identifier, otherwise it will look for an index "id" in the array
	 * @param string $name	The descriptive name for the user
	 * @param int $userId	Used to specify internal ID (defaults to null, which means record will be located by using $user)
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access public
	 * @since 1.0
	 */
	function saveUser($user, $name = null, $userId = null)
	{
		$this->_initializeAdmin();
		
		$userValue = (is_array($user) && isset($user['id']) ? $user['id'] : strval($user));
		$userName = (isset($name) ? $name : (is_array($user) && isset($user['name']) ? $user['name'] : ucwords($userValue)) );
		
		if (empty($userValue))
		{
			return false;
		}
		
		$result = false;
		
		// Save ARO (add or edit)
		
		if (!isset($userId))
		{
			$userId = $this->gacl_admin->get_object_id('user', $userValue, 'ARO');
		}
		
		if ($userId === false)
		{
			$result = $this->gacl_admin->add_object('user', $userName, $userValue, 0, 0, 'ARO');
		}
		else
		{
			$result = $this->gacl_admin->edit_object($userId, 'user', $userName, $userValue, 0, 0, 'ARO');
		}
		
		$result = ($result !== false);
		
		if ($result === false)
		{
			return $result;
		}
		
		return $result;
	}
	
	/**
	 * Sets the controller.
	 *
	 * @param mixed	$controller	Controller using the component
	 *
	 * @access public
	 * @since 1.0
	 */
	function setController(&$controller)
	{
		$this->controller =& $controller;
	}
	
	/**
	 * Checks if the specified controller/action should be accessed by current user.
	 *
	 * @param string $controller	Controller to check access on (e.g: 'Posts')
	 * @param string $action	Action to check access on (e.g: 'view')
	 *
	 * @return bool	true if access is granted, false otherwise
	 *
	 * @access private
	 * @since 1.0
	 */
	function _check($controller, $action)
	{
		$this->_initialize();
		
		if (!$this->isInstalled())
		{
			return false;
		}
		
		// Get the user ID
		
		$currentUser = null;
		
		if (is_array($this->controller->gacl) && (isset($this->controller->gacl['user']) || (isset($this->controller->gacl['get']) && is_array($this->controller->gacl['get']) && isset($this->controller->gacl['get']['type']) && strcasecmp($this->controller->gacl['get']['type'], 'session') != 0)))
		{
			if (isset($this->controller->gacl['user']))
			{
				$currentUser = $this->controller->gacl['user'];
			}
			else if (strcmp($this->controller->gacl['get']['type'], 'callback') == 0 && isset($this->controller->gacl['get']['value']))
			{
				$method = $this->controller->gacl['get']['value'];
				
				$currentUser = $this->controller->$method();
			}
		}
		
		if (!isset($currentUser))
		{
			// Default way of getting the user
			
			$session = null;
			
			if (isset($this->controller->Session))
			{
				$session =& $this->controller->Session;
			}
			else
			{
				loadComponent('Session');
				
				$session =& new SessionComponent();
			}
			
			if (isset($session))
			{
				$key = 'User';
				
				if (is_array($this->controller->gacl) && isset($this->controller->gacl['get']) && is_array($this->controller->gacl['get']) && isset($this->controller->gacl['get']['type']) && strcasecmp($this->controller->gacl['get']['type'], 'session') == 0 && isset($this->controller->gacl['get']['value']))
				{
					$key = $this->controller->gacl['get']['value'];
				}
				
				if ($session->check($key))
				{
					$currentUser = $session->read($key);
				}
			}
		}
		
		// Check access
		
		$result = false;
		
		if (isset($currentUser) && $currentUser !== false)
		{
			$currentUser = (is_array($currentUser) && isset($currentUser['id']) ? $currentUser['id'] : strval($currentUser));
			
			$result = $this->access($currentUser, $controller, $action);
		}
		
		return $result;
	}
	
	/**
	 * Get the data for the specified group id.
	 *
	 * @param array $group	Group ID.
	 * @param string $type	Group type (ARO or AXO)
	 *
	 * @return array	Indexed array with 'id', 'parent_id', 'value', and 'name'
	 *
	 * @access private
	 * @since 1.0
	 */
	function _getGroupData($group, $type = 'ARO')
	{
		if (!in_array($type, array('ARO', 'AXO')))
		{
			return false;
		}

		if (!isset($this->_data))
		{
			$this->_data = array();
		}
		else if (!isset($this->_data['group']))
		{
			$this->_data['group'] = array(
				'ARO' => array(),
				'AXO' => array()
			);
		}
		
		if (!isset($this->_data['group'][$type][$group]))
		{
			$groupData = $this->gacl_admin->get_group_data($group, $type);
			
			if ($groupData !== false)
			{
				$groupData = array (
					'id' => $groupData[0],
					'parent_id' => $groupData[1],
					'value' => $groupData[2],
					'show_value' => $groupData[2],
					'name' => $groupData[3]
				);
			}
	
			$this->_data['group'][$type][$group] = $groupData;
		}
		
		return $this->_data['group'][$type][$group];
	}
	
	/**
	 * Return the root as returned by GaclAdmin::sort() in a threaded array.
	 *
	 * @param array $root	Element to transform.
	 * @param array $groups	Array of all elements as returned by GaclAdmin::sort()
	 *
	 * @return array	Threaded array
	 *
	 * @access private
	 * @since 1.0
	 */
	function _getGroupThread($root, &$groups)
	{
		$element = array();
		
		foreach($root as $index => $value)
		{
			if (strcmp($index, 'children') != 0)
			{
				$element[$index] = $value;
			}
		}
		
		$element['children'] = array();
		
		foreach($root['children'] as $index => $rootChild)
		{
			if (isset($groups[$rootChild['id']]))
			{
				$child = $this->_getGroupThread($groups[$rootChild['id']], $groups);
			}
			else
			{
				$child = $rootChild;
				
				$child['id'] = $child['show_value'];
				unset($child['value']);
				unset($child['show_value']);
				unset($child['parent_id']);
			}
			
			$element['children'][] = $child;
		}
		
		$element['id'] = $element['show_value'];
		unset($element['value']);
		unset($element['show_value']);
		unset($element['parent_id']);
		
		return $element;
	}
	
	/**
	 * Get path to controllers inside available plugins.
	 *
	 * @return array	An array of paths
	 *
	 * @access private
	 * @since 1.0
	 */
	function _getPluginControllerPaths()
	{
		$result = array();
		
		$path = APP . 'plugins';
		$dir = opendir($path);
		
		while(false !== ($directory = readdir($dir)))
		{
			if (is_dir($path . DS . $directory) && $directory != '.' && $directory != '..' && file_exists($path . DS . $directory . DS . 'controllers'))
			{
				$result[] = $path . DS . $directory . DS . 'controllers';
			}
		}
		
		closedir($dir);
		
		return $result;
	}
	
	/**
	 * Initialize the component, setting the phpGacl object and installing database schema if necessary.
	 *
	 * @access private
	 * @since 1.0
	 */
	function _initialize()
	{
		if (!isset($this->gacl))
		{
			$gaclClassFile = APP . 'vendors' . DS . 'phpgacl' . DS . 'gacl_api.class.php';
			
			if (@!file_exists($gaclClassFile))
			{
				$gaclClassFile = VENDORS . 'phpgacl' . DS . 'gacl_api.class.php';
				
				if (@!file_exists($gaclClassFile))
				{
					return false;
				}
			}
			
			vendor('phpgacl/gacl.class');
			
			$settings = $this->getDatabaseSettings();
			
			$this->gacl =& new gacl($settings);
		}
	}
	
	/**
	 * Initialize the phpGaclAdmin object.
	 *
	 * @access private
	 * @since 1.0
	 */
	function _initializeAdmin()
	{
		$this->_initialize();
		
		if (!isset($this->gacl_admin))
		{
			vendor('phpgacl/gacl_api.class');
			
			$settings = $this->getDatabaseSettings();
			
			// Reutilize current ADODB connection
			
			$settings['db'] =& $this->gacl->db;
			
			// Set up the phpGaclAdmin object
			
			$this->gacl_admin =& new gacl_api($settings);
		}
	}
	
	/**
	 * Install the database schema using ADODB and schema provided by phpGACL.
	 *
	 * @param array $settings	CakePHP database settings.
	 *
	 * @return bool	true if schema installed, false otherwise.
	 *
	 * @access private
	 * @since 1.0
	 */
	function _install($settings)
	{
		vendor('phpgacl/adodb/adodb-xmlschema.inc');
		
		// Let's install schema using ADODB
		
		if (class_exists('adoSchema'))
		{
			// Find schema file
			
			$schemaFile = APP . 'vendors' . DS . 'phpgacl' . DS . 'schema.xml';
			
			if (!file_exists($schemaFile))
			{
				$schemaFile = VENDORS . 'phpgacl' . DS . 'schema.xml';
				
				if (!file_exists($schemaFile))
				{
					return false;
				}
			}
			
			// Set up schema and install
			
			$schema = new adoSchema($this->gacl->db);
			
			$schema->SetPrefix($settings['db_table_prefix'], false);
			$schema->ParseSchema($schemaFile);
			
			$result = $schema->ExecuteSchema();
			
			if ($result == 2)
			{
				$result = $this->_installData();
			}
			else
			{
				$result = false; 
			}
			
			// Return success or failure
			
			return $result;
		}
		
		return false;
	}
	
	/**
	 * Sets up phpGACL with initial standard data:
	 * - An ACO section called "access", with an ACO object called "execute" assigned
	 * - An AXO group called "controller"
	 * - One AXO section for every controller
	 * - One AXO object for every action, assigned to the section identified by its controller, and AXO group "controller"
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access private
	 * @since 1.0
	 */
	function _installData()
	{
		$this->_initializeAdmin();
		
		// Set up virtual roots
		
		$result = $this->gacl_admin->add_group('root', 'AXO Root', 0, 'AXO');
		
		if ($result === false)
		{
			return false;
		}
		
		$result = $this->gacl_admin->add_group('root', 'ARO Root', 0, 'ARO');
		
		if ($result === false)
		{
			return false;
		}
		
		// Set up ACO section 'access'
		
		$result = $this->gacl_admin->add_object_section('Access', 'access', 0, 0, 'ACO');
		
		if ($result === false)
		{
			return false;
		}
		
		// Set up ARO section 'user'
		
		$result = $this->gacl_admin->add_object_section('User', 'user', 0, 0, 'ARO');
		
		if ($result === false)
		{
			return false;
		}
		
		// Set up ACO objects (access types)
		
		$result = $this->gacl_admin->add_object('access', 'Execute', 'execute', 0, 0, 'ACO');
		
		if ($result === false)
		{
			return false;
		}
		
		// Set up AXO group 'controller'
		
		$result = $this->gacl_admin->add_group('controller', 'Application Controllers', $this->gacl_admin->get_root_group_id('AXO'), 'AXO');
		
		return true;
	}
	
	/**
	 * Sets up data for the specified controller:
	 * - One AXO section named after the controller class name (excluding the "Controller" ending part)
	 * - One AXO object for every action, assigned to the section identified by its controller, and group "controller"
	 *
	 * @param string $controllerFile	Controller file name (e.g: my_controller.php)
	 * @param array $actions	Only add this specific set of actions (defaults to all actions in controller)
	 * @param string $controllerPath	Path where the controller can be found (defaults to CONTROLLERS)
	 *
	 * @return bool	true on success, false on failure
	 *
	 * @access private
	 * @since 1.0
	 */
	function _installDataController($controllerFile, $actions = null, $controllerPath = CONTROLLERS)
	{
		$this->_initializeAdmin();
		
		// Get all methods in controller
		
		list($name) = explode('.', $controllerFile);
		
		$controllerName = Inflector::camelize(substr($name, 0, strrpos($name, '_controller')));

		if (!isset($actions) || !is_array($actions))
		{
			$actions = $this->_getControllerActions($controllerName, $controllerPath);
		}
		
		// Set up controller ID as the underscore version of its name
		
		$controller = Inflector::underscore($controllerName);
		$controllerValue = $this->sectionControllerPrefix . $controller;
		
		// Add controller as an AXO section (if not already added)
		
		if ($this->gacl_admin->get_object_section_section_id(null, $controllerValue, 'AXO') === false)
		{
			if (strpos($controllerPath, APP . 'plugins') === 0)
			{
				$pluginName = substr($controllerPath, strlen(APP . 'plugins') + 1);
				$pluginName = Inflector::camelize(substr($pluginName, 0, strpos($pluginName, DS)));
				
				$controllerName = $pluginName . $this->controllerNamePluginSeparator . $controllerName;
			}
			
			$result = $this->gacl_admin->add_object_section($controllerName, $controllerValue, 0, 0, 'AXO');
			
			if ($result === false)
			{
				return false;
			}
		}
		
		// Remove actions that were previously added and are no longer there
		
		$elements = $this->gacl_admin->get_objects($controllerValue, 0, 'AXO');
		
		if ($elements !== false && isset($elements[$controllerValue]))
		{
			$elements = $elements[$controllerValue];
			
			$diff = array_diff($elements, $actions);
			
			foreach($diff as $action)
			{
				$actionValue = $action;
				
				$actionId = $this->gacl_admin->get_object_id($controllerValue, $actionValue, 'AXO');
				
				if ($actionId !== false)
				{
					$result = $this->gacl_admin->del_object($actionId, 'AXO', true);
				}
			}
		}
		
		// Set up AXO objects (actions)
		
		foreach($actions as $action)
		{
			$actionValue = $action;
			$actionName = $action . '()';
			
			// Only add if it hasn't been already added
			
			if ($this->gacl_admin->get_object_id($controllerValue, $actionValue, 'AXO') === false)
			{
				// Add AXO object
				
				$result = $this->gacl_admin->add_object($controllerValue, $actionName, $actionValue, 0, 0, 'AXO');
				
				if ($result === false)
				{
					return false;
				}
				
				// Assign to AXO group
				
				$controllerGroupId = $this->gacl_admin->get_group_id('controller', null, 'AXO');
				
				if ($controllerGroupId === false)
				{
					$controllerGroupId = $this->gacl_admin->get_root_group_id('AXO');
				}
				
				$result = $this->gacl_admin->add_group_object($controllerGroupId, $controllerValue, $actionValue, 'AXO');
				
				if ($result === false)
				{
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Get all CakePHP actions defined in a controller.
	 *
	 * @param string $controller	Controller name (e.g: Posts)
	 * @param string $controllerPath	Path where the controller can be found (defaults to CONTROLLERS)
	 *
	 * @return array	Actions that can be called in controller
	 *
	 * @access private
	 * @since 1.0
	 */
	function _getControllerActions($controller, $controllerPath = CONTROLLERS)
	{
		$controllerClassName = $controller . 'Controller';
		$controllerFile = Inflector::underscore($controller) . '_controller.php';
		
		if ($controllerPath[strlen($controllerPath) - 1] != DS)
		{
			$controllerPath .= DS;
		}
		
		if (strpos($controllerPath, APP . 'plugins') === 0)
		{
			$pluginName = str_replace(dirname(dirname($controllerPath)), '', $controllerPath);
			
			if ($pluginName[0] == DS)
			{
				$pluginName = substr($pluginName, 1);
			}
			
			list($pluginName) = explode(DS, $pluginName);
			
			$parentClassName = Inflector::camelize($pluginName) . 'AppController';
			$parentFile = dirname($controllerPath) . DS . Inflector::underscore($parentClassName) . '.php';
			
			if (file_exists($parentFile))
			{
				require_once($parentFile);
				
				$parentActions = get_class_methods($parentClassName);
			}
		}
		
		if (!isset($parentActions))
		{
			$parentActions = get_class_methods('AppController');
		}
		
		require_once($controllerPath . $controllerFile);
		
		$actions = get_class_methods($controllerClassName);
		
		// Remove inherited methods
		
		$actions = array_diff($actions, $parentActions);
		
		// Remove constructor
		
		$constructorKey = array_search(strtolower($controllerClassName), $actions);
		
		if ($constructorKey !== false)
		{
			unset($actions[$constructorKey]);
		}
		
		// Remove private methods, and underscore actions
		
		$result = array();
		
		foreach($actions as $index => $action)
		{
			if ($action[0] != '_')
			{
				$result[] = Inflector::underscore($action);
			}
		}
		
		return $result;
	}
	
	/**
	 * Parses a controller list, that can be either a set of controllers, controller => action, or controller => array of actions.
	 *
	 * @param array $controllers	List of controllers
	 * @param bool $useAclActions	true if only get actions defined in phpGacl, false if all actions in controller should be gotten
	 *
	 * @return array	Consolidated list of controllers
	 *
	 * @access private
	 * @since 1.0
	 */
	function _parseControllerList($controllers, $useAclActions = true)
	{
		if (!is_array($controllers))
		{
			$controllers = array( $controllers );
		}
		
		foreach($controllers as $controllerName => $value)
		{
			if (!is_array($value) && is_string($controllerName))
			{
				$value = array( $value );
			}
			else if (!is_string($controllerName))
			{
				unset($controllers[$controllerName]);
				
				$controllers[$value] = array();
				$controllerName = $value;
				$value = array();
			}
			
			if ($useAclActions)
			{
				$controllerValue = $this->sectionControllerPrefix . Inflector::underscore($controllerName);
			}
			else
			{
				$controllerValue = $controllerName;
			}
			
			if (empty($value))
			{
				if ($useAclActions)
				{
					// Get all actions for controller defined in GACL
					
					$elements = $this->gacl_admin->get_objects($controllerValue, 0, 'AXO');
					
					if ($elements === false)
					{
						return false;
					}
				
					$value = $elements[$controllerValue];
				}
				else
				{
					$value = $this->_getControllerActions($controllerName);
				}
			}
			
			unset($controllers[$controllerName]);
			$controllers[$controllerValue] = $value;
		}
		
		return $controllers;
	}
}

?>