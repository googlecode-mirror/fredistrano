<?php
class Project extends AppModel {

	var $name = 'Project';

	var $validate = array (
		'name' => array (
			array (
				VALID_NOT_EMPTY,
				'Veuillez saisir un nom de projet'
			)
			,
			array (array('isUnique', array('name')), 'Ce nom de projet est déjà utilisé.')
		),
		'svn_url' => array (
			array (
				VALID_NOT_EMPTY,
				'Veuillez saisir l\'url du repository pour ce projet'
			)
		),
		'prd_url' => array (
			array (
				VALID_NOT_EMPTY,
				'Veuillez saisir l\'url de l\'application sur le serveur de production'
			)
		),
		'prd_path' => array (
			array (
				VALID_NOT_EMPTY,
				'Veuillez saisir le nom du dossier de l\'application sur le serveur de production'
			)
		)
	);
	
	var $hasMany = array (
		'DeploymentLog' => array (
			'className' => 'DeploymentLog',
			'order'     => 'DeploymentLog.created DESC',
			'limit'     => '5',
			'foreignKey' => 'project_id',
			'dependent' => false
		)
	);


}
?>