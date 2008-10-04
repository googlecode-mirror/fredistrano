<?php
class AdministrationController extends AppController {
	
	var $uses = array ('Project','DeploymentLog','Deployment');

	var $authLocal = array (
		'Administration' => array (
			'administration'
		)
	);
	
	function beforeRender() {
		parent::beforeRender();
		
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array (
			'text' => 'Actions'
		);

		$this->set("context_menu", $tab);
	}
	
	function index() {}

} // Administration
?>