<?php

/**
 * PhpgaclAco Model class file.
 *
 * Model for PhpgaclAco.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Sypad, Inc.
 * @link			http://www.sypad.com SYPAD
 * @package			sypad
 * @subpackage		sypad.plugins.phpgacl.models
 * @since			Sypad v 1.0
 */

/**
 * Model to manipulate phpGACL ACOs.
 *
 * @author  Mariano Iglesias
 * @package sypad
 * @subpackage sypad.plugins.phpgacl.models
 * @since   1.0
 */
class PhpgaclAco extends PhpgaclAppModel
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
	var $name = 'PhpgaclAco';
	
	/**
	 * Table used by this model.
	 *
	 * @var string
	 * @since 1.0
	 */
	var $useTable = 'aco';
	
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
	 * Runs before a validation is executed. Used to validate for uniqueness of records.
	 *
	 * @access protected
	 * @since 1.0
	 */
	function beforeValidate()
	{
		if ($this->isDuplicate('value'))
		{
			$this->invalidate('value_unique');
		}
		
		return parent::beforeValidate();
	}
}

?>