<?php
class Group extends AppModel {

	var $name = 'Group';

//	var $validate = array (
//		'name' => array (
//			array (
//				VALID_NOT_EMPTY,
//				LANG_PLEASEENTERANAME
//			),
//			array (
//				array (
//					'isUnique',
//					array (
//						'name'
//					)
//				),
//				LANG_GROUPALREADYEXISTS
//			)
//		)
//	);

	var $hasAndBelongsToMany = array (
		'User' => array (
			'className' => 'User',
			'joinTable' => 'groups_users',
			'foreignKey' => 'group_id',
			'associationForeignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'unique' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
		
	);

	var $hasOne = array (
		'ParentGroup' => array (
			'className' => 'Group',
			'foreignKey' => 'parent_id',
			
		),
		
	);
	
}
?>