<?php

/**
 * Base model for Phpgacl models.
 *
 * All Phpgacl plugin models inherit this class.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Sypad, Inc.
 * @link			http://www.sypad.com SYPAD
 * @package			sypad
 * @subpackage		sypad.plugins.phpgacl
 * @since			Sypad v 1.0
 */

/**
 * The base model for all models in the plugin.
 *
 * @author  Mariano Iglesias
 * @package sypad
 * @subpackage sypad.plugins.phpgacl
 * @since   1.0
 */
class PhpgaclAppModel extends AppModel
{
	/**
	 * Override constructor to select appropiate DB connection.
	 *
	 * @access protected
	 * @since 1.0
	 */
	function __construct()
	{
		uses('model' . DS . 'connection_manager');
		
		// Get database configuration as defined in CakePHP
		
		$connectionManager = ConnectionManager::getInstance();		
		$connectionName = (isset($connectionManager->config->gacl) ? 'gacl' : 'default');
			
		// Set which connection we should use
		
		$this->useDbConfig = $connectionName;
		
		return parent::__construct();
	}
	
	/**
	 * Check to see if current submitted record is duplicate on specified $field. It will only perform the checking
	 * if specified field has no other validation errors.
	 *
	 * @param string $field	The field to check for uniqueness
	 *
	 * @return bool	true if it's duplicate, false otherwise
	 *
	 * @access protected
	 * @since 1.0
	 */
	function isDuplicate($field)
	{
		$duplicate = false;
		
		$currentId = $this->_getCurrentId();
		
		if (!empty($this->data[$this->name][$field]) && (!isset($this->validationErrors) || !is_array($this->validationErrors) || !isset($this->validationErrors[$field])))
		{
			$fields = array(
				$this->name . '.' . $field => $this->data[$this->name][$field],
				$this->name . '.' . $this->primaryKey => '!= ' . (!empty($currentId) ? $currentId : 'NULL')
			);
			
			if ($this->hasAny($fields))
			{
				$duplicate = true;
			}
		}
		
		return $duplicate;
	}
	
	/**
	 * Helper function to get ID of current submitted model. It checks $this->data, and if not available $this->id.
	 *
	 * @return int	Submitted ID
	 *
	 * @access protected
	 * @since 1.0
	 */
	function _getCurrentId()
	{
		$currentId = (isset($this->data[$this->name][$this->primaryKey]) ? $this->data[$this->name][$this->primaryKey] : (!empty($this->id) ? $this->id : null));
		
		return $currentId;
	}
}

?>