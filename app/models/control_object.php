<?php
/* SVN FILE: $Id$ */
/**
 * 
 * 
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			app
 * @subpackage		app.models
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.models
 */
class ControlObject extends AppModel {

	var $name = 'ControlObject';

	var $validate = array(
	    'name' => array(
	        'rule1' => array(
	            'rule' => 'alphaNumeric',
	            'required' => true
	        ),
	        'rule2' => array(
	            'rule' => 'isUnique'
	        )
	    )
	);

	var $belongsTo = array (
		'ParentControlObject' => array (
			'className' => 'ControlObject',
			'foreignKey' => 'parent_id',
			
		),
		
	);
	
}// ControlObject
?>