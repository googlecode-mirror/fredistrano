<?php

/**
 * PhpgaclAcoSection Model class file.
 *
 * Model for PhpgaclAcoSection.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Sypad, Inc.
 * @link			http://www.sypad.com SYPAD
 * @package			sypad
 * @subpackage		sypad.plugins.phpgacl.models
 * @since			Sypad v 1.0
 */

/**
 * Model to manipulate phpGACL ACO sections.
 *
 * @author  Mariano Iglesias
 * @package sypad
 * @subpackage sypad.plugins.phpgacl.models
 * @since   1.0
 */
class PhpgaclAcoSection extends PhpgaclAppModel
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
	var $name = 'PhpgaclAcoSection';
	
	/**
	 * Table used by this model.
	 *
	 * @var string
	 * @since 1.0
	 */
	var $useTable = 'aco_sections';
	
	/**
	 * Models that belong to this model. This model's construct will set up appropiate finderQuery.
	 *
	 * @var array
	 * @since 1.0
	 */
	var $hasMany = array(
		'PhpgaclAco' => array (
			'className' => 'PhpgaclAco',
			'foreignKey' => 'section_value'
		)
	);
	
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
	 * We need to set up a finderQuery for this model so we do it just before the *first* call to a find method.
	 *
	 * @param array $queryData	The query that is about to be executed
	 *
	 * @return bool	Perform the query, or quit
	 *
	 * @access protected
	 * @since 1.0
	 */
	function beforeFind($queryData)
	{
		if (empty($this->hasMany['PhpgaclAco']['finderQuery']))
		{
			$foreignModelClass = $this->hasMany['PhpgaclAco']['className'];
			
			$db =& ConnectionManager::getDataSource($this->useDbConfig);
			
			loadModel($foreignModelClass);
			$foreignModel =& new $foreignModelClass();
			
			$finderQuery = 	'SELECT ' . 
												$db->name($foreignModelClass) . '.*' . 
											' FROM ' .
												$db->name($this->tablePrefix . $foreignModel->table) . ' AS ' . $db->name($foreignModel->name) .
												',' .
												$db->name($this->tablePrefix . $this->table) . ' AS ' . $db->name($this->name) .
											' WHERE ' . 
												$db->name($this->name) . '.' . $db->name('id') . ' = {$__cakeID__$}' .
											' AND ' .
												$db->name($foreignModel->name) . '.' . $db->name('section_value') . ' = ' . $this->escapeField('value');
			
			$this->hasMany['PhpgaclAco']['finderQuery'] = $finderQuery;
		}
		
		return parent::beforeFind($queryData);
	}	
	
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
		
		return parent::beforeValidate();
	}
}

?>