<?php
class Group extends AppModel {
	var $name = 'Group';

	var $validate = array(
		'group_name' => array (
			array (
				VALID_NOT_EMPTY,
				'Veuillez saisir un nom.'
			)
		)
	);
}
?>