<?php
class AcliteAppController extends AppController {

	var $name = 'AcliteApp';

	var $helpers = array (
		'Html'
	);

	var $components = array (
		'Acl'
	);

	var $authGlobal = array (
		'AcliteApp' => array (
			'authorizations'
		)
	);

}
?>