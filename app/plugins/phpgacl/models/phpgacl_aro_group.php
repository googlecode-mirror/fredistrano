<?php

/**
 * PhpgaclAroGroup Model class file.
 *
 * Model for PhpgaclAroGroup.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Sypad, Inc.
 * @link			http://www.sypad.com SYPAD
 * @package			sypad
 * @subpackage		sypad.plugins.phpgacl.models
 * @since			Sypad v 1.0
 */

/**
 * Model to manipulate phpGACL ARO groups.
 *
 * @author  Mariano Iglesias
 * @package sypad
 * @subpackage sypad.plugins.phpgacl.models
 * @since   1.0
 */
class PhpgaclAroGroup extends PhpgaclAppModel
{
	/**#@+
	 * @access protected
	 */
	/**
	 * Name for the model.
	 *
	 * @var string
	 * @since 1.0
	 */
	var $name = 'PhpgaclAroGroup';
	
	/**
	 * Table used by this model.
	 *
	 * @var string
	 * @since 1.0
	 */
	var $useTable = 'aro_groups';
	
	/**
	 * Validation rules for this model.
	 *
	 * @var array
	 * @since 1.0
	 */
	var $validate = array(
		'value' => '/[a-z0-9\_\-]{1,}$/i',
		'name' => VALID_NOT_EMPTY
	);
	/**#@-*/
	
	/**
	 * Runs before a validation is executed. Used to validate for uniqueness of records and prevent parent_id looping.
	 *
	 * @access protected
	 * @since 1.0
	 */
	function beforeValidate()
	{
		$currentId = $this->_getCurrentId();
		
		if ($this->isDuplicate('value'))
		{
			$this->invalidate('value_unique');
		}
		
		if (!empty($this->data[$this->name]['parent_id']) && !empty($currentId) && $currentId == $this->data[$this->name]['parent_id'])
		{
			$this->invalidate('parent_id');
		}
		
		return parent::beforeValidate();
	}
}

?>