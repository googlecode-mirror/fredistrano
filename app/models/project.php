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
			array (
				'isProjectNameUnique',
				'Ce projet existe déjà'
			)
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
		),
		'config_path' => array (
			array (
				VALID_NOT_EMPTY,
				'Veuillez saisir le nom du dossier des fichiers de configuration de l\'application'
			)
		)

	);
	
	var $hasMany = array (
		'DeploymentLog' => array (
			'className' => 'DeploymentLog',
			'order'     => 'DeploymentLog.date DESC',
			'limit'     => '5',
			'foreignKey' => 'project_id',
			'dependent' => false
		)
	);

	// basée sur http://cakebaker.wordpress.com/2006/02/06/yet-another-data-validation-approach/
	// rajouté par mes soins une condition pour ne pas tester le username lors d'une modification
	function isProjectNameUnique() {
		if ( $this->id == null ) //Adding a project
        {
            return (!$this->hasAny( array( 'Project.name' => $this->data[$this->name]['name'] ) ));
        }
        else //Editing a project
        {
            return (!$this->hasAny( array( 'Project.name' => $this->data[$this->name]['name'], 'Project.id' => '!='.$this->data[$this->name]['id'] ) ) );
        }
	}

}
?>