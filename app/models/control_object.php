<?php
class ControlObject extends AppModel {

	var $name = 'ControlObject';

	var $validate = array (
		'name' => array (
			array (
				VALID_NOT_EMPTY,
				LANG_PLEASEENTERANAME
			),
			array (
				array (
					'isUnique',
					array (
						'name'
					)
				),
				LANG_OBJECTNAMEALREADYEXIST
			)
		),
		
	);

	var $hasOne = array (
		'ParentControlObject' => array (
			'className' => 'ControlObject',
			'foreignKey' => 'parent_id',
			
		),
		
	);
	
}// ControlObject
?>