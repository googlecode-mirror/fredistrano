<?php


/* SVN FILE: $Id: app_model.php 4409 2007-02-02 13:20:59Z phpnut $ */
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2007, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2007, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision: 4409 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2007-02-02 07:20:59 -0600 (Fri, 02 Feb 2007) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Application model for Cake.
 *
 * This is a placeholder class.
 * Create the same file in app/app_model.php
 * Add your application-wide methods to the class, your models will inherit them.
 *
 * @package		cake
 * @subpackage	cake.cake
 */
class AppModel extends Model {

	/**
	 * Support UTF-8
	 */
	static $utf8IsSet = false;

	function __construct() {
		$db = & ConnectionManager :: getDataSource($this->useDbConfig);
		if (!self :: $utf8IsSet and (get_class($db) != 'LdapSource')) {
			$this->execute("SET NAMES 'utf8'");
			self :: $utf8IsSet = true;
		}
		parent :: __construct();
	}

	/**
	 * Vérifie l'unicité des valeurs d'un champ
	 */
	function isUnique($params) {
		$fieldName = $params[0];
		if ($this-> { $this->primaryKey } == null) // add
			return (!$this->hasAny(array ($this->name . '.' . $fieldName => $this->data[$this->name][$fieldName])));
		else // edit
			return (!$this->hasAny(array (
				$this->name . '.' . $fieldName => $this->data[$this->name][$fieldName],
				$this->name . '.' . $this->primaryKey => '!=' . $this->data[$this->name][$this->primaryKey]
			)));
	}// isUnique

	/**
	 * Validation avancée
	 * basée sur http://cakebaker.42dh.com/2006/02/06/yet-another-data-validation-approach/
	 */
	function invalidFields($data = array ()) {
		if (!$this->beforeValidate())
			return false;
	
		if (!isset ($this->validate))
			return true;
	
		if (!empty ($this->validationErrors))
			return $this->validationErrors;
	
		if (isset ($this->data))
			$data = array_merge($data, $this->data);
	
		$errors = array ();
		$this->set($data);
	
		foreach ($data as $table => $field) {
			foreach ($this->validate as $field_name => $validators) {
				foreach ($validators as $validator) {
					if (isset ($validator[0])) {
						if (is_array($validator[0])) {
							$function_name = $validator[0][0];
							$params = $validator[0][1];
	
							if (method_exists($this, $function_name)) {
								if (isset ($data[$table][$field_name]) and !call_user_func(array (
										$this,
										$function_name
									), $params)) {
									if (!isset ($errors[$field_name])) {
										$errors[$field_name] = isset ($validator[1]) ? $validator[1] : 1;
									}
								}
							}
						} else {
							if (isset ($data[$table][$field_name]) && !preg_match($validator[0], $data[$table][$field_name])) {
								if (!isset ($errors[$field_name])) {
									$errors[$field_name] = isset ($validator[1]) ? $validator[1] : 1;
								}
							}
						}
					}
				}
			}
		}
	
		$this->validationErrors = $errors;
		return $errors;
	}// invalidFields

}// AppModel
?>