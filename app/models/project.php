<?php
class Project extends AppModel {

	var $name = 'Project';

	var $validate = array (
		'name' => array (
			array (
				VALID_NOT_EMPTY,
				LANG_ENTERPROJECTNAME
			)
			,
			array (array('isUnique', array('name')), LANG_PROJECTNAMEALREADYEXISTS)
		),
		'svn_url' => array (
			array (
				VALID_NOT_EMPTY,
				LANG_ENTERURLREPOSITORYFORTHISPROJECT
			)
		),
		'prd_url' => array (
			array (
				VALID_NOT_EMPTY,
				LANG_ENTERPRODUCTIONURL
			)
		),
		'prd_path' => array (
			array (
				VALID_NOT_EMPTY,
				LANG_ENTERAPPLICATIONDIRECTORY
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