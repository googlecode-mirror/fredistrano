<?php

/**
 * PhpgaclUsers Controller class file.
 *
 * Controller for PhpgaclUsers actions.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Sypad, Inc.
 * @link			http://www.sypad.com SYPAD
 * @package			sypad
 * @subpackage		sypad.plugins.phpgacl.controllers
 * @since			Sypad v 1.0
 */

/**
 * Actions to manage groups and users in phpGACL.
 *
 * @author  Mariano Iglesias
 * @package sypad
 * @subpackage sypad.plugins.phpgacl.controllers
 * @since   1.0
 */
class PhpgaclUsersController extends PhpgaclAppController
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
	var $name = 'PhpgaclUsers';
	
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
		$this->set('title', 'phpGACL :: User and Group Management');
		
		return parent::beforeRender();
	}
	
	/**
	 * Delete a group.
	 *
	 * @param string $value	Group identifier.
	 *
	 * @access public
	 * @since 1.0
	 */
	function delete_group($value)
	{
		if (isset($value))
		{
			$record = $this->PhpgaclAroGroup->findByValue($value);
			
			if ($record === false || strcasecmp($record['PhpgaclAroGroup']['value'], 'root') == 0)
			{
				$this->redirect($this->pluginUrl . '/phpgaclUsers/groups');
				exit;
			}
		}
		
		if (!empty($this->data))
		{
			$this->_avoidTimeout();
			
			$result = $this->PhpGacl->delGroup($value);
			
			$this->Session->setFlash(($result ? 'The group (and its associated data) was successfully deleted' : 'Could not delete group, or part of its associated data'));
			$this->redirect($this->pluginUrl . '/phpgaclUsers/groups');
			exit;
		}
		
		$this->set('record', $record);
		$this->set('phpgacl_title', 'Delete Group');
	}
	
	/**
	 * Delete a user.
	 *
	 * @param string $value	User identifier.
	 *
	 * @access public
	 * @since 1.0
	 */
	function delete_user($value)
	{
		if (isset($value))
		{
			$record = $this->PhpgaclAro->findByValue($value);
			
			if ($record === false)
			{
				$this->redirect($this->pluginUrl . '/phpgaclUsers/users');
				exit;
			}
		}
		
		if (!empty($this->data))
		{
			$this->_avoidTimeout();
			
			$result = $this->PhpGacl->delUser($value);
			
			$this->Session->setFlash(($result ? 'The user (and its associated data) was successfully deleted' : 'Could not delete user, or part of its associated data'));
			$this->redirect($this->pluginUrl . '/phpgaclUsers/users');
			exit;
		}
		
		$this->set('record', $record);
		$this->set('phpgacl_title', 'Delete User');
	}
	
	/**
	 * Add or edit a group.
	 *
	 * @param string $value	Group identifier.
	 *
	 * @access public
	 * @since 1.0
	 */
	function edit_group($value = null)
	{
		if (isset($value))
		{
			$record = $this->PhpgaclAroGroup->findByValue($value);
			
			if ($record === false || in_array(strtolower($record['PhpgaclAxoGroup']['value']), array('root')))
			{
				unset($record);
			}
		}
		
		if (!empty($this->data))
		{
			if (isset($record))
			{
				$this->data['PhpgaclAroGroup']['id'] = $record['PhpgaclAroGroup']['id'];
			}
			
			$this->PhpgaclAroGroup->set($this->data);
			
			if ($this->PhpgaclAroGroup->validates())
			{
				$data = $this->data['PhpgaclAroGroup'];
				
				$result = $this->PhpGacl->saveGroup($data['value'], $data['name'], null, 'ARO', (isset($record) ? $record['PhpgaclAroGroup']['id'] : null), $data['parent_id']);
				
				$this->Session->setFlash(($result ? 'The group information was successfully saved' : 'Could not save group information'));
				$this->redirect($this->pluginUrl . '/phpgaclUsers/groups');
				exit;
			}
		}
		else if (isset($record))
		{
			$this->data = $record;
		}
		
		$groups = $this->PhpgaclAroGroup->findAllThreaded(null, null, 'PhpgaclAroGroup.name');
		
		$this->set('groups', $groups);
		$this->set('phpgacl_title', (isset($value) ? 'Edit' : 'Add') . ' Group');
	}
	
	/**
	 * Add or edit a user.
	 *
	 * @param string $value	User identifier.
	 *
	 * @access public
	 * @since 1.0
	 */
	function edit_user($value = null)
	{
		if (isset($value))
		{
			$record = $this->PhpgaclAro->findByValue($value);
			
			if ($record === false)
			{
				unset($record);
			}
			else
			{
				$record['PhpgaclAroGroup'] = array (
					'groups' => $this->PhpGacl->getUserGroups($value)
				);
			}
		}
		
		if (!empty($this->data))
		{
			if (isset($record))
			{
				$this->data['PhpgaclAro']['id'] = $record['PhpgaclAro']['id'];
			}
			
			$this->PhpgaclAro->set($this->data);
			
			if ($this->PhpgaclAro->validates())
			{
				$data = $this->data['PhpgaclAro'];
				
				$result = $this->PhpGacl->saveUser($data['value'], $data['name'], (isset($record) ? $record['PhpgaclAro']['id'] : null));
				
				if ($result)
				{
					$groups = array();
					
					if (isset($this->data['PhpgaclAroGroup']) && isset($this->data['PhpgaclAroGroup']['groups']))
					{
						$groups = $this->data['PhpgaclAroGroup']['groups'];
					}
					
					$result = $this->PhpGacl->assignGroup($data['value'], $groups);
				}
				
				$this->Session->setFlash(($result ? 'The user information was successfully saved' : 'Could not save user information'));
				$this->redirect($this->pluginUrl . '/phpgaclUsers/users');
				exit;
			}
		}
		else if (isset($record))
		{
			$this->data = $record;
		}
		
		$groups = $this->PhpgaclAroGroup->findAllThreaded(null, null, 'PhpgaclAroGroup.name');
		
		if ($groups !== false && isset($groups[0]) && strcasecmp($groups[0]['PhpgaclAroGroup']['value'], 'root') == 0)
		{
			$groups = $groups[0]['children'];
		}
		
		if (!isset($groups) || !is_array($groups))
		{
			$groups = array();
		}
		
		$this->set('groups', $groups);
		$this->set('phpgacl_title', (isset($value) ? 'Edit' : 'Add') . ' User');
	}
	
	/**
	 * List all groups with links for editing, removing, and creation.
	 *
	 * @access public
	 * @since 1.0
	 */
	function groups()
	{
		$conditions = array(
			'PhpgaclAroGroup.value' => '!= root'
		);
		
		$records = $this->PhpgaclAroGroup->findAll($conditions, 'PhpgaclAroGroup.value, PhpgaclAroGroup.name', null, null, 1, 0);
		
		$this->set('records', $records);
		$this->set('phpgacl_title', 'Manage Groups');
	}
	
	/**
	 * Import groups and users into phpGACL.
	 *
	 * @access public
	 * @since 1.0
	 */
	function import()
	{
		$this->_avoidTimeout();
		
		$models = $this->_getModels();
		
		if (!empty($this->data))
		{
			if (!empty($this->data['Model']['group']) || !empty($this->data['Model']['user']))
			{
				// Import groups
				
				$imported = array();
				
				if (!empty($this->data['Model']['group']))
				{
					$imported['group'] = $this->_importData($models, $this->data['Model'], 'group');
				}
				
				if (!empty($this->data['Model']['user']))
				{
					$imported['user'] = $this->_importData($models, $this->data['Model'], 'user');
				}
				
				$message = '';
				
				if (isset($imported['group']) && $imported['group'] !== false)
				{
					if ($imported['group']['available'] > 0)
					{
						$message .= '<strong>' . $imported['group']['imported'] . '</strong> Group record' . ($imported['group']['imported'] > 1 ? 's were' : ' was') . ' imported, out of <strong>' . $imported['group']['available'] . '</strong> available. ';
					}
					else
					{
						$message .= 'Eventhough you selected to import groups, there were no records available. ';
					}
				}
				else if (isset($imported['group']))
				{
					$message .= 'There was a problem while trying to import groups. ';
				}
				
				if (isset($imported['user']) && $imported['user'] !== false)
				{
					if ($imported['user']['available'] > 0)
					{
						$message .= '<strong>' . $imported['user']['imported'] . '</strong> User record' . ($imported['user']['imported'] > 1 ? 's were' : ' was') . ' imported, out of <strong>' . $imported['user']['available'] . '</strong> available. ';
					}
					else
					{
						$message .= 'Eventhough you selected to import users, there were no records available. ';
					}
				}
				else if (isset($imported['user']))
				{
					$message .= 'There was a problem while trying to import users. ';
				}
				
				$this->Session->setFlash($message);
				$this->redirect($this->pluginUrl);
				exit;
			}

			$this->set('form_message', 'You have selected to import neither Groups nor Users. If that\'s what you intended, then there\'s nothing left to do and you can <a href="' . $this->base . '">Go back to the Control Panel</a>');
			$this->set('data', $this->data);
		}
		
		$this->set('models', $models);
		$this->set('phpgacl_title', 'Import your groups / users into phpGACL');
	}
	
	/**
	 * List all users with links for editing, removing, and creation.
	 *
	 * @access public
	 * @since 1.0
	 */
	function users()
	{
		$conditions = array();
		
		$records = $this->PhpgaclAro->findAll($conditions, 'PhpgaclAro.value, PhpgaclAro.name', null, null, 1, 0);
		
		$this->set('records', $records);
		$this->set('phpgacl_title', 'Manage Users');
	}
	
	/**
	 * Get list of available model files in current CakePHP application.
	 *
	 * @return array	List of model files.
	 *
	 * @access private
	 * @since 1.0
	 */
	function _getModels()
	{
		$models = array();
		
		$files = listClasses(MODELS);
		
		foreach($files as $file)
		{
			list($modelValue) = explode('.', $file);
			
			$modelName = Inflector::camelize($modelValue);
			
			if (loadModel($modelName))
			{
				$modelInstance = new $modelName();
				
				$fields = $modelInstance->loadInfo();
				
				$columns = array();
				
				foreach($fields->value as $field)
				{
					if (in_array($field['type'], array('integer', 'string')))
					{
						$columns[$field['name']] = Inflector::camelize($field['name']);
					}
				}
				
				if (count($columns) > 0)
				{
					if (count($columns) > 0)
					{
						// Sort by name
						
						$name = array();
						
						foreach($columns as $index => $column)
						{
							$name[$index] = $column['name'];
						}
						
						array_multisort($name, SORT_ASC, $columns);
					}
			
					$model = array();
					
					$model['value'] = $modelValue;
					$model['name'] = $modelName;
					$model['columns'] = $columns;
					
					$models[] = $model;
				}
			}
		}
		
		if (count($models) > 0)
		{
			// Sort by name
			
			$name = array();
			
			foreach($models as $index => $model)
			{
				$name[$index] = $model['name'];
			}
			
			array_multisort($name, SORT_ASC, $models);
		}
		
		return $models;
	}
	
	/**
	 * Import groups/users into phpGACL using the selected model and fields.
	 *
	 * @param array $models	List of models available
	 * @param array $data	Data as submitted by form
	 * @param string $container	What type of data we're importing (group/user)
	 *
	 * @return mixed	An indexed array with keys 'available', and 'imported', or false if failure.
	 *
	 * @access private
	 * @since 1.0
	 */
	function _importData($models, $data, $container = 'group')
	{
		$imported = false;
		
		$currentModelValue = $data[$container];
		$currentFieldIdentifier = $data[$container . '_identifier'];
		$currentFieldName = $data[$container . '_name'];
		
		$currentModel = null;
		
		// Locate model
		
		foreach($models as $model)
		{
			if ($model['value'] == $currentModelValue)
			{
				$currentModel = $model;
				break;
			}
		}
		
		// Only import when model was found and selected fields are valid
		
		if (isset($currentModel) && array_key_exists($currentFieldIdentifier, $currentModel['columns']) && array_key_exists($currentFieldName, $currentModel['columns']))
		{
			loadModel($currentModel['name']);
			$currentModelInstance = new $currentModel['name']();
			
			$records = $currentModelInstance->findAll(null, $currentModel['name'] . '.' . $currentFieldIdentifier . ', ' . $currentModel['name'] . '.' . $currentFieldName, null, null, 1, 0);
			
			if ($records !== false)
			{
				$imported = array(
					'available' => count($records),
					'imported' => 0
				);
				
				foreach($records as $record)
				{
					$currentValue = $record[$currentModel['name']][$currentFieldIdentifier];
					$currentName = $record[$currentModel['name']][$currentFieldName];
					
					$result = false;
					
					switch($container)
					{
						case 'group':
							$result = $this->PhpGacl->saveGroup($currentValue, $currentName);
							break;
							
						case 'user':
							$result = $this->PhpGacl->saveUser($currentValue, $currentName);
							break;
					}
					
					if ($result)
					{
						$imported['imported']++;
					}
				}
			}
		}
		
		return $imported;
	}
}

?>