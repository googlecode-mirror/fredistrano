<?php
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