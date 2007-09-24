<?php
class HomeController extends AppController {
	var $uses = array ();

	function index() {
		$this->set('loginError', false);

	} // index

} // HomeController
?>