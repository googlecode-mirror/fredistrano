<?php

/**
 * PhpgaclElements Controller class file.
 *
 * Controller for PhpgaclElements actions.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Sypad, Inc.
 * @link			http://www.sypad.com SYPAD
 * @package			sypad
 * @subpackage		sypad.plugins.phpgacl.controllers
 * @since			Sypad v 1.0
 */

/**
 * Actions to manage elements in phpGACL.
 *
 * @author  Mariano Iglesias
 * @package sypad
 * @subpackage sypad.plugins.phpgacl.controllers
 * @since   1.0
 */
class PhpgaclElementsController extends PhpgaclAppController
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
	var $name = 'PhpgaclElements';
	
	/**
	 * Components used by this controller.
	 *
	 * @var array
	 * @since 1.0
	 */
	var $components = array( 'PhpGacl', 'Session' );
	
	/**
	 * Models used by this controller.
	 *
	 * @var array
	 * @since 1.0
	 */
	var $uses = array ('PhpgaclAco', 'PhpgaclAcoSection', 'PhpgaclAxo', 'PhpgaclAxoGroup', 'PhpgaclAxoSection');
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
		$this->set('title', 'phpGACL :: Protectable Elements');
		
		return parent::beforeRender();
	}
	
	/**
	 * List all ACO sections with links for editing, removing, and creation.
	 *
	 * @access public
	 * @since 1.0
	 */
	function aco_sections()
	{
		// Keep ID on list of fields as it is needed by the model to build the hasMany finderQuery
		
		$records = $this->PhpgaclAcoSection->findAll(null, 'PhpgaclAcoSection.id, PhpgaclAcoSection.value, PhpgaclAcoSection.name', null, null, 1, 1);
		
		if ($records !== false)
		{
			foreach($records as $index => $record)
			{
				$records[$index]['protected'] = false;
				
				if (in_array(strtolower($record['PhpgaclAcoSection']['value']), array('access')))
				{
					$records[$index]['protected'] = true;
				}
			}
		}
		
		$this->set('records', $records);
		$this->set('phpgacl_title', 'Manage Access Types');
	}
	
	/**
	 * List all AXO sections with links for editing, removing, and creation.
	 *
	 * @access public
	 * @since 1.0
	 */
	function axo_sections()
	{
		// Only fetch AXO sections that are not controllers.
		// Keep ID on list of fields as it is needed by the model to build the hasMany finderQuery
		
		$conditions = array(
			'not' => array('PhpgaclAxoSection.value' => 'LIKE controller.%')
		);
		
		$records = $this->PhpgaclAxoSection->findAll($conditions, 'PhpgaclAxoSection.id, PhpgaclAxoSection.value, PhpgaclAxoSection.name', null, null, 1, 1);
		
		if ($records !== false)
		{
			foreach($records as $index => $record)
			{
				$records[$index]['protected'] = false;
				
				if (in_array(strtolower($record['PhpgaclAxoSection']['value']), array('access')))
				{
					$records[$index]['protected'] = true;
				}
			}
		}
		
		$this->set('records', $records);
		$this->set('phpgacl_title', 'Manage Controllable Element Sections');
	}
	
	/**
	 * List all ACOs assigned to a section, with links for editing, removing, and creation.
	 *
	 * @param string $value	Section identifier
	 *
	 * @access public
	 * @since 1.0
	 */
	function acos($value)
	{
		$section = $this->PhpgaclAcoSection->findByValue($value);
		
		if ($section === false)
		{
			$this->redirect($this->pluginUrl . '/phpgaclElements/aco_sections');
			exit;
		}
		
		$conditions = array('PhpgaclAco.section_value' => $value);
		
		$records = $this->PhpgaclAco->findAll($conditions, 'PhpgaclAco.value, PhpgaclAco.name', null, null, 1, 0);
		
		if ($records !== false)
		{
			foreach($records as $index => $record)
			{
				$records[$index]['protected'] = false;
				
				if (in_array(strtolower($record['PhpgaclAco']['value']), array('execute')))
				{
					$records[$index]['protected'] = true;
				}
			}
		}
		
		$this->set('section', $section);
		$this->set('records', $records);
		$this->set('phpgacl_title', $section['PhpgaclAcoSection']['name'] . ($section['PhpgaclAcoSection']['name'][strlen($section['PhpgaclAcoSection']['name']) - 1] == 's' ? '\'' : '\'s') . ' Access Types');
	}
	
	/**
	 * List all AXOs assigned to a section, with links for editing, removing, and creation.
	 *
	 * @param string $value	Section identifier
	 *
	 * @access public
	 * @since 1.0
	 */
	function axos($value)
	{
		$section = $this->PhpgaclAxoSection->findByValue($value);
		
		if ($section === false)
		{
			$this->redirect($this->pluginUrl . '/phpgaclElements/axo_sections');
			exit;
		}
		
		$conditions = array(
			array ('PhpgaclAxoGroup.value' => '!= root'),
			array ('PhpgaclAxoGroup.value' => '!= controller')
		);
		
		$groups = $this->PhpgaclAxoGroup->findAll($conditions, 'PhpgaclAxoGroup.value, PhpgaclAxoGroup.name', null, null, 1, 0);
		
		if ($groups !== false && count($groups) > 0)
		{		
			$conditions = array('PhpgaclAxo.section_value' => $value);
			
			$records = $this->PhpgaclAxo->findAll($conditions, 'PhpgaclAxo.value, PhpgaclAxo.name', null, null, 1, 0);
			
			if ($records !== false)
			{
				foreach($records as $index => $record)
				{
					$records[$index]['protected'] = false;
					
					if (in_array(strtolower($record['PhpgaclAxo']['value']), array('execute')))
					{
						$records[$index]['protected'] = true;
					}
				}
			}
			
			$this->set('groups', $groups);
			$this->set('records', $records);
		}
		
		$this->set('section', $section);
		$this->set('phpgacl_title', $section['PhpgaclAxoSection']['name'] . ($section['PhpgaclAxoSection']['name'][strlen($section['PhpgaclAxoSection']['name']) - 1] == 's' ? '\'' : '\'s') . ' Controllable Elements');
	}
	
	/**
	 * Delete an ACO.
	 *
	 * @param string $sectionValue	ACO section identifier.
	 * @param string $value	ACO identifier.
	 *
	 * @access public
	 * @since 1.0
	 */
	function delete_aco($sectionValue, $value)
	{
		$section = $this->PhpgaclAcoSection->findByValue($sectionValue);
		$record = $this->PhpgaclAco->findByValue($value);
		
		if ($section === false || $record === false || in_array(strtolower($record['PhpgaclAco']['value']), array('execute')))
		{
			$this->redirect($this->pluginUrl . '/phpgaclElements/aco_sections');
			exit;
		}
		
		if (!empty($this->data))
		{
			$this->_avoidTimeout();
			
			$result = $this->PhpGacl->delObject($section['PhpgaclAcoSection']['value'], $record['PhpgaclAco']['value'], 'ACO', true);
			
			$this->Session->setFlash(($result ? 'The access type (and its associated data) was successfully deleted' : 'Could not delete access type, or part of its associated data'));
			$this->redirect($this->pluginUrl . '/phpgaclElements/acos/' . $section['PhpgaclAcoSection']['value']);
			exit;
		}
		
		$this->set('section', $section);
		$this->set('record', $record);
		$this->set('phpgacl_title', 'Delete Access Type');
	}
	
	/**
	 * Delete an ACO section.
	 *
	 * @param string $value	ACO section identifier.
	 *
	 * @access public
	 * @since 1.0
	 */
	function delete_aco_section($value)
	{
		$record = $this->PhpgaclAcoSection->findByValue($value);
		
		if ($record === false || in_array(strtolower($record['PhpgaclAcoSection']['value']), array('access')))
		{
			$this->redirect($this->pluginUrl . '/phpgaclElements/aco_sections');
			exit;
		}
		
		if (!empty($this->data))
		{
			$this->_avoidTimeout();
			
			$result = $this->PhpGacl->delSection($value, 'ACO', true);
			
			$this->Session->setFlash(($result ? 'The access type section (and its associated data) was successfully deleted' : 'Could not delete access type section, or part of its associated data'));
			$this->redirect($this->pluginUrl . '/phpgaclElements/aco_sections');
			exit;
		}
		
		$this->set('record', $record);
		$this->set('phpgacl_title', 'Delete Access Type Section');
	}
	
	/**
	 * Delete an AXO.
	 *
	 * @param string $sectionValue	AXO section identifier.
	 * @param string $value	AXO identifier.
	 *
	 * @access public
	 * @since 1.0
	 */
	function delete_axo($sectionValue, $value)
	{
		$section = $this->PhpgaclAxoSection->findByValue($sectionValue);
		$record = $this->PhpgaclAxo->findByValue($value);
		
		$conditions = array(
			array ('PhpgaclAxoGroup.value' => '!= root'),
			array ('PhpgaclAxoGroup.value' => '!= controller')
		);
		
		$groups = $this->PhpgaclAxoGroup->findAll($conditions, 'PhpgaclAxoGroup.value, PhpgaclAxoGroup.name', null, null, 1, 0);
		
		if ($section === false || $record === false || $groups === false || count($groups) == 0)
		{
			$this->redirect($this->pluginUrl . '/phpgaclElements/axo_sections');
			exit;
		}
		
		if (!empty($this->data))
		{
			$this->_avoidTimeout();
			
			$result = $this->PhpGacl->delObject($section['PhpgaclAxoSection']['value'], $record['PhpgaclAxo']['value'], 'AXO', true);
			
			$this->Session->setFlash(($result ? 'The Controllable Element (and its associated data) was successfully deleted' : 'Could not delete Controllable Element, or part of its associated data'));
			$this->redirect($this->pluginUrl . '/phpgaclElements/axos/' . $section['PhpgaclAxoSection']['value']);
			exit;
		}
		
		$this->set('section', $section);
		$this->set('record', $record);
		$this->set('phpgacl_title', 'Delete Controllable Element');
	}
	
	/**
	 * Delete an AXO section.
	 *
	 * @param string $value	AXO section identifier.
	 *
	 * @access public
	 * @since 1.0
	 */
	function delete_axo_section($value)
	{
		$record = $this->PhpgaclAxoSection->findByValue($value);
		
		if ($record === false || strpos(strtolower($record['PhpgaclAxoSection']['value']), $this->PhpGacl->sectionControllerPrefix) === 0)
		{
			$this->redirect($this->pluginUrl . '/phpgaclElements/axo_sections');
			exit;
		}
		
		if (!empty($this->data))
		{
			$this->_avoidTimeout();
			
			$result = $this->PhpGacl->delSection($value, 'AXO', true);
			
			$this->Session->setFlash(($result ? 'The Controllable Element section (and its associated data) was successfully deleted' : 'Could not delete Controllable Element section, or part of its associated data'));
			$this->redirect($this->pluginUrl . '/phpgaclElements/axo_sections');
			exit;
		}
		
		$this->set('record', $record);
		$this->set('phpgacl_title', 'Delete Controllable Element Section');
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
		$record = $this->PhpgaclAxoGroup->findByValue($value);
		
		if ($record === false || in_array(strtolower($record['PhpgaclAxoGroup']['value']), array('controller', 'root')))
		{
			$this->redirect($this->pluginUrl . '/phpgaclElements/groups');
			exit;
		}
		
		if (!empty($this->data))
		{
			$this->_avoidTimeout();
			
			$result = $this->PhpGacl->delGroup($value, true, 'AXO');
			
			$this->Session->setFlash(($result ? 'The category (and its associated data) was successfully deleted' : 'Could not delete category, or part of its associated data'));
			$this->redirect($this->pluginUrl . '/phpgaclElements/groups');
			exit;
		}
		
		$this->set('record', $record);
		$this->set('phpgacl_title', 'Delete Category');
	}
	
	/**
	 * Add or edit an ACO.
	 *
	 * @param string $sectionValue	ACO section identifier.
	 * @param string $value	ACO identifier.
	 *
	 * @access public
	 * @since 1.0
	 */
	function edit_aco($sectionValue, $value = null)
	{
		$section = $this->PhpgaclAcoSection->findByValue($sectionValue);
		
		if ($section === false)
		{
			$this->redirect($this->pluginUrl . '/phpgaclElements/aco_sections');
			exit;
		}
		
		if (isset($value))
		{
			$record = $this->PhpgaclAco->findByValue($value);
			
			if ($record === false)
			{
				unset($record);
			}
		}
		
		if (!empty($this->data))
		{
			if (isset($record))
			{
				$this->data['PhpgaclAco']['id'] = $record['PhpgaclAco']['id'];
				
				// If it's a protected group, only let user edit descriptive name
				
				if (in_array(strtolower($record['PhpgaclAco']['value']), array('execute')))
				{
					$this->data['PhpgaclAco']['value'] = $record['PhpgaclAco']['value'];
				}
			}
			
			$this->PhpgaclAco->set($this->data);
			
			if ($this->PhpgaclAco->validates())
			{
				$data = $this->data['PhpgaclAco'];
				
				$result = $this->PhpGacl->saveObject($section['PhpgaclAcoSection']['value'], $data['value'], $data['name'], 'ACO', (isset($record) ? $record['PhpgaclAco']['id'] : null));
				
				$this->Session->setFlash(($result ? 'The Access Type information was successfully saved' : 'Could not save Access Type information'));
				$this->redirect($this->pluginUrl . '/phpgaclElements/acos/' . $section['PhpgaclAcoSection']['value']);
				exit;
			}
		}
		else if (isset($record))
		{
			$this->data = $record;
			
			if (in_array(strtolower($record['PhpgaclAco']['value']), array('execute')))
			{
				$this->set('protected', true);
			}
		}
		
		$this->set('section', $section);
		$this->set('phpgacl_title', (isset($value) ? 'Edit' : 'Add') . ' Access Type');
	}
	
	/**
	 * Add or edit an ACO section.
	 *
	 * @param string $value	ACO section identifier.
	 *
	 * @access public
	 * @since 1.0
	 */
	function edit_aco_section($value = null)
	{
		if (isset($value))
		{
			$record = $this->PhpgaclAcoSection->findByValue($value);
		}
		
		if (!empty($this->data))
		{
			if (isset($record))
			{
				$this->data['PhpgaclAcoSection']['id'] = $record['PhpgaclAcoSection']['id'];
				
				// If it's a protected group, only let user edit descriptive name
				
				if (in_array(strtolower($record['PhpgaclAcoSection']['value']), array('access')))
				{
					$this->data['PhpgaclAcoSection']['value'] = $record['PhpgaclAcoSection']['value'];
				}
			}
			
			$this->PhpgaclAcoSection->set($this->data);
			
			if ($this->PhpgaclAcoSection->validates())
			{
				$data = $this->data['PhpgaclAcoSection'];
				
				$result = $this->PhpGacl->saveSection($data['value'], $data['name'], 'ACO', (isset($record) ? $record['PhpgaclAcoSection']['id'] : null));
				
				$this->Session->setFlash(($result ? 'The Access Type section information was successfully saved' : 'Could not save Access Type section information'));
				$this->redirect($this->pluginUrl . '/phpgaclElements/aco_sections');
				exit;
			}
		}
		else if (isset($record))
		{
			$this->data = $record;
			
			if (in_array(strtolower($record['PhpgaclAcoSection']['value']), array('access')))
			{
				$this->set('protected', true);
			}
		}
		
		$this->set('phpgacl_title', (isset($value) ? 'Edit' : 'Add') . ' Access Type Section');
	}
	
	/**
	 * Add or edit an AXO.
	 *
	 * @param string $sectionValue	AXO section identifier.
	 * @param string $value	AXO identifier.
	 *
	 * @access public
	 * @since 1.0
	 */
	function edit_axo($sectionValue, $value = null)
	{
		$section = $this->PhpgaclAxoSection->findByValue($sectionValue);
		
		$conditions = array(
			array ('PhpgaclAxoGroup.value' => '!= root'),
			array ('PhpgaclAxoGroup.value' => '!= controller')
		);
		
		$groups = $this->PhpgaclAxoGroup->findAll($conditions, 'PhpgaclAxoGroup.value, PhpgaclAxoGroup.name', null, null, 1, 0);
		
		if (!empty($this->data) && !empty($this->data['PhpgaclAxoGroup']['group']))
		{
			$selectedGroup = $this->PhpgaclAxoGroup->findById($this->data['PhpgaclAxoGroup']['group']);
			
			if ($selectedGroup === false)
			{
				unset($selectedGroup);
			}
		}
		
		if ($section === false || $groups === false || count($groups) == 0 || (isset($selectedGroup) && $selectedGroup['PhpgaclAxoGroup']['value'] == 'controller'))
		{
			$this->redirect($this->pluginUrl . '/phpgaclElements/axos/' . $sectionValue);
			exit;
		}
		
		if (isset($value))
		{
			$record = $this->PhpgaclAxo->findByValue($value);
			
			if ($record === false)
			{
				unset($record);
			}
			else
			{
				// Get assigned AXO group
				
				$assignedGroups = $this->PhpGacl->getObjectGroups($section['PhpgaclAxoSection']['value'], $record['PhpgaclAxo']['value'], 'AXO', true);
				
				if ($assignedGroups === false)
				{
					unset($assignedGroups);
				}
			}
		}
		
		if (!empty($this->data))
		{
			if (isset($record))
			{
				$this->data['PhpgaclAxo']['id'] = $record['PhpgaclAxo']['id'];
			}
			
			$this->PhpgaclAxo->set($this->data);
			
			if ($this->PhpgaclAxo->validates())
			{
				$data = $this->data['PhpgaclAxo'];
				
				$selectedGroups = array(
					$selectedGroup['PhpgaclAxoGroup']['value']
				);
				
				$result = $this->PhpGacl->saveObject($section['PhpgaclAxoSection']['value'], $data['value'], $data['name'], 'AXO', (isset($record) ? $record['PhpgaclAxo']['id'] : null));
				
				if ($result)
				{
					$result = $this->PhpGacl->assignGroupObject($section['PhpgaclAxoSection']['value'], $data['value'], $selectedGroups, 'AXO');
				}
				
				$this->Session->setFlash(($result ? 'The Controllable Element information was successfully saved' : 'Could not save Controllable Element information'));
				$this->redirect($this->pluginUrl . '/phpgaclElements/axos/' . $section['PhpgaclAxoSection']['value']);
				exit;
			}
		}
		else if (isset($record))
		{
			$this->data = $record;
			
			if (isset($assignedGroups))
			{
				$this->data['PhpgaclAxoGroup'] = array(
					'group' => $assignedGroups[0]
				);
			}
		}
		
		$conditions = array(
			'PhpgaclAxoGroup.value' => '!= controller'
		);
		
		$groups = $this->PhpgaclAxoGroup->findAllThreaded($conditions, null, 'PhpgaclAxoGroup.name');
		$groups = $groups[0]['children'];
		
		$this->set('section', $section);
		$this->set('groups', $groups);
		$this->set('phpgacl_title', (isset($value) ? 'Edit' : 'Add') . ' Controllable Element');
	}
	
	/**
	 * Add or edit an AXO section.
	 *
	 * @param string $value	AXO section identifier.
	 *
	 * @access public
	 * @since 1.0
	 */
	function edit_axo_section($value = null)
	{
		if (isset($value))
		{
			$record = $this->PhpgaclAxoSection->findByValue($value);
			
			if ($record === false || strpos(strtolower($record['PhpgaclAxoSection']['value']), $this->PhpGacl->sectionControllerPrefix) === 0)
			{
				$this->redirect($this->pluginUrl . '/phpgaclElements/axo_sections');
				exit;
			}
		}
		
		if (!empty($this->data))
		{
			if (isset($record))
			{
				$this->data['PhpgaclAxoSection']['id'] = $record['PhpgaclAxoSection']['id'];
			}
			
			$this->PhpgaclAxoSection->set($this->data);
			
			if ($this->PhpgaclAxoSection->validates())
			{
				$data = $this->data['PhpgaclAxoSection'];
				
				$result = $this->PhpGacl->saveSection($data['value'], $data['name'], 'AXO', (isset($record) ? $record['PhpgaclAxoSection']['id'] : null));
				
				$this->Session->setFlash(($result ? 'The Controllable Element section information was successfully saved' : 'Could not save Controllable Element section information'));
				$this->redirect($this->pluginUrl . '/phpgaclElements/axo_sections');
				exit;
			}
		}
		else if (isset($record))
		{
			$this->data = $record;
		}
		
		$this->set('phpgacl_title', (isset($value) ? 'Edit' : 'Add') . ' Controllable Element Section');
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
			$record = $this->PhpgaclAxoGroup->findByValue($value);
			
			if ($record === false || in_array(strtolower($record['PhpgaclAxoGroup']['value']), array('root')))
			{
				unset($record);
			}
		}
		
		if (!empty($this->data))
		{
			if (isset($record))
			{
				$this->data['PhpgaclAxoGroup']['id'] = $record['PhpgaclAxoGroup']['id'];
				
				// If it's a protected group, only let user edit descriptive name
				
				if (in_array(strtolower($record['PhpgaclAxoGroup']['value']), array('controller')))
				{
					$this->data['PhpgaclAxoGroup']['value'] = $record['PhpgaclAxoGroup']['value'];
					$this->data['PhpgaclAxoGroup']['parent_id'] = $record['PhpgaclAxoGroup']['parent_id'];
				}
			}
			
			$this->PhpgaclAxoGroup->set($this->data);
			
			if ($this->PhpgaclAxoGroup->validates())
			{
				$data = $this->data['PhpgaclAxoGroup'];
				
				$result = $this->PhpGacl->saveGroup($data['value'], $data['name'], null, 'AXO', (isset($record) ? $record['PhpgaclAxoGroup']['id'] : null), $data['parent_id']);
				
				$this->Session->setFlash(($result ? 'The category information was successfully saved' : 'Could not save category information'));
				$this->redirect($this->pluginUrl . '/phpgaclElements/groups');
				exit;
			}
		}
		else if (isset($record))
		{
			$this->data = $record;
			
			if (in_array(strtolower($record['PhpgaclAxoGroup']['value']), array('controller')))
			{
				$this->set('protected', true);
			}
		}
		
		$groups = $this->PhpgaclAxoGroup->findAllThreaded(null, null, 'PhpgaclAxoGroup.name');
		
		$this->set('groups', $groups);
		$this->set('phpgacl_title', (isset($value) ? 'Edit' : 'Add') . ' Category');
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
			'PhpgaclAxoGroup.value' => '!= root'
		);
		
		$records = $this->PhpgaclAxoGroup->findAll($conditions, 'PhpgaclAxoGroup.value, PhpgaclAxoGroup.name', null, null, 1, 0);
		
		if ($records !== false)
		{
			foreach($records as $index => $record)
			{
				$records[$index]['protected'] = false;
				
				if (in_array(strtolower($record['PhpgaclAxoGroup']['value']), array('controller', 'root')))
				{
					$records[$index]['protected'] = true;
				}
			}
		}
		
		$this->set('records', $records);
		$this->set('phpgacl_title', 'Manage Categories');
	}
	
	/**
	 * Select which controllers and actions should be defined in phpGACL.
	 *
	 * @access public
	 * @since 1.0
	 */
	function index()
	{
		// Process submission
		
		if (!empty($this->data))
		{
			$controllers = $this->_getControllers();
			
			// Get list of controllers and actions to save
			
			$submittedControllers = array();
			
			if (isset($this->data['Controller']['actions']))
			{
				foreach($this->data['Controller']['actions'] as $element)
				{
					list($controllerValue, $actionValue) = explode('::', $element);
					
					if (!isset($submittedControllers[$controllerValue]))
					{
						$submittedControllers[$controllerValue] = array();
					}
					
					$submittedControllers[$controllerValue][] = $actionValue;
				}
			}
			
			// Delete controllers that are no longer selected (only those not belonging to this plugin)
			
			foreach($controllers as $controller)
			{
				if ($controller['saved'] && !$controller['isPhpgaclPlugin'] && !isset($submittedControllers[$controller['value']]))
				{
					$result = $this->PhpGacl->delController($controller['name']);
				}
			}
			
			// Save selected controllers
			
			foreach($submittedControllers as $controllerValue => $actions)
			{
				// Locate controller
				
				$currentController = null;
				
				foreach($controllers as $controller)
				{
					if ($controller['value'] == $controllerValue)
					{
						$currentController = $controller;
						break;
					}
				}
				
				if (isset($currentController))
				{
					$result = $this->PhpGacl->saveController($currentController['name'], $actions, $currentController['path']);
				}
			}
			
			$this->Session->setFlash('Your selected controller/actions were saved to phpGACL');
			$this->redirect($this->pluginUrl);
			exit;
		}
		
		$result = $this->_getControllers(true);
		
		$controllers = $result[':application:'];
		
		unset($result[':application:']);
		
		$plugins = $result;
		
		if (isset($plugins) && count($plugins) > 0)
		{
			$this->set('plugins', $plugins);
		}
		
		if (isset($controllers) && count($controllers) > 0)
		{
			$this->set('controllers', $controllers);
		}
		
		$this->set('phpgacl_title', 'Manage Controllers and Actions');
	}
	
	/**
	 * Get list of available controllers indicating which are already saved. Returns an array of controllers, where each controller
	 * is an indexed array with the following elements: 'name', 'value', 'path', 'isPhpgaclPlugin', 'actions' (array of actions, where 
	 * each action is an indexed array with indexes 'name', 'value').
	 *
	 * @param bool $group	Group controllers by plugins (defaults to false, if true returned array is same as explained but grouped by one index ':application:', and one for each plugin)
	 *
	 * @return array	List of controllers
	 *
	 * @access private
	 * @since 1.0
	 */
	function _getControllers($group = false)
	{
		// Get list of available controllers
		
		$controllers = $this->PhpGacl->getControllers(true);
		
		foreach($controllers as $index => $controller)
		{
			// Mark plugin controllers
			
			if (strpos($controller['path'], APP . 'plugins') === 0)
			{
				$pluginName = substr($controller['path'], strlen(APP . 'plugins') + 1);
				
				$pluginName = Inflector::camelize(substr($pluginName, 0, strpos($pluginName, DS)));
				
				$controllers[$index]['isPlugin'] = true;
				$controllers[$index]['plugin'] = $pluginName;
			}
			else
			{
				$controllers[$index]['isPlugin'] = false;
			}
			
			// Mark those which belong to this component
			
			if ($controller['path'] == dirname(__FILE__))
			{
				$controllers[$index]['isPhpgaclPlugin'] = true;
			}
			else
			{
				$controllers[$index]['isPhpgaclPlugin'] = false;
			}
			
			$controllers[$index]['actions'] = $this->PhpGacl->getControllerActions($controller['name'], true, $controller['path']);
		}
		
		// Get list of saved controllers in phpGACL
		
		$savedControllers = $this->PhpGacl->getControllers();
		
		foreach($savedControllers as $index => $controller)
		{
			$savedControllers[$index]['actions'] = $this->PhpGacl->getControllerActions($controller['name']);
		}
		
		$savedElements = array();
		
		foreach($savedControllers as $controller)
		{
			$savedElements[] = $controller['value'];
			
			foreach($controller['actions'] as $action)
			{
				$savedElements[] = $controller['value'] . '::' . $action['value'];
			}
		}
		
		unset($savedControllers);
		
		// Mark controllers and actions that were already saved
		
		foreach($controllers as $index => $controller)
		{
			$controllers[$index]['saved'] = in_array($controller['value'], $savedElements);
			
			foreach($controller['actions'] as $actionIndex => $action)
			{
				$controllers[$index]['actions'][$actionIndex]['saved'] = in_array($controller['value'] . '::' . $action['value'], $savedElements);
				$controllers[$index]['actions'][$actionIndex]['form_value'] = $controller['value'] . '::' . $action['value'];
			}
		}
		
		unset($savedElements);
		
		if ($group)
		{
			$grouped = array(
				':application:' => array()
			);
			
			foreach($controllers as $controller)
			{
				if (!$controller['isPlugin'])
				{
					$grouped[':application:'][] = $controller;
				}
				else
				{
					if (!isset($grouped[$controller['plugin']]))
					{
						$grouped[$controller['plugin']] = array();
					}
					
					$grouped[$controller['plugin']][] = $controller;
				}
			}
			
			$controllers = $grouped;
		}
		
		return $controllers;
	}
}

?>