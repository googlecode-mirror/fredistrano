<?php
class HomeController extends AppController {
	var $uses = array ();

	function index() {
		$this->set('loginError', false);

	} // index
	
	function switchLanguage(){
		$_SESSION['userPreferedLanguage'] = $this->params['pass'][0];
		$this->redirect($this->referer());
	}

} // HomeController
?>