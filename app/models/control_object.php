<?php
class ControlObject extends AppModel {

	var $name = 'ControlObject';

	var $validate = array (
		'name' => array (
			array (
				VALID_NOT_EMPTY,
				'Veuillez saisir un nom.'
			),
			array (
				array (
					'isUnique',
					array (
						'name'
					)
				),
				'Un objet de ce nom existe déjà.'
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