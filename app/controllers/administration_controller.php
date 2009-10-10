<?php
/* SVN FILE: $Id$ */
/**
 * Controller for accessing administration ressources
 * 
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			app
 * @subpackage		app.controllers
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Controller for accessing administration ressources
 *
 * @package		app
 * @subpackage	app.controllers
 */
class AdministrationController extends AppController {
	
	var $uses = array ('DeploymentLog');

	var $authLocal = array (
		'Administration' => array (
			'administration'
		)
	);
	
	/**
	 * Default action
	 */
	function index() {
		$crumbs[] = array(
			'name' 		=> __('Administration', true),
			'link'		=> null,
			'options'	=> null
			);
		$this->set('crumbs', $crumbs);
	}

	/**
	 * Default action
	 */
	function cleanOrphanLogs() {
		$count = $this->DeploymentLog->cleanOrphans();
		
		$this->Session->setFlash(sprintf(__('%d orphan logs have been deleted.', true), $count));
		$this->redirect('/administration');
		exit;
	}// cleanOrphelinsLogs
	
}// Administration
?>