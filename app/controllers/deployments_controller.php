<?php
/* SVN FILE: $Id$ */
/**
 * Controller that provides access to the core features of fredistrano
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
 * Controller that provides access to the core features of fredistrano
 *
 * @package		app
 * @subpackage	app.controllers
 */
class DeploymentsController extends AppController {

	var $name = 'Deployments';
	
	var $helpers = array ('Ajax','Pagination');

	var $uses = array ('Deployment');
	
	var $authLocal = array (
		'Deployments' => array (
			'buinessData'
		)
	);
	
	function beforeFilter() {
		parent::beforeFilter();
		
		$id = null;
		if (isset($this->data['Project']['id'])) {
			$id = $this->data['Project']['id'];
		} else if (isset($this->params['pass'][0])) {
			$id = $this->params['pass'][0];
		} 
		
		if (is_null($id)){
			$this->Session->setFlash(__('Missing id.', true));
			$this->redirect('/projects/index');
			exit();
		}
		$this->_setContext();
	}// par

	// Initialize runs ---------------------------------------
	/**
	 * Deploy manually a project
	 * @param string $id ID of the project to be deployed
	 */
	function runManual($id = null) {
		// View
		$this->layout = 'ajax';
		
		// Init deployment uuid
		$this->Session->write('Deployment.uuid', $this->Deployment->generateUuid($id));
		
		$this->set('id', $id);
	}// runManual
	
	/**
	 * Deploy automatically a project
	 * @param string $id ID of the project to be deployed
	 */
	function runAutomatic($id = null) {
		$this->layout = 'ajax';

		// Init deployment uuid
		$this->Session->write('Deployment.uuid', $this->Deployment->generateUuid($id));
		$this->_setContext();
		
		// Run step
		$options = Configure::read('Deployment.options');
		$log = $this->Deployment->process($this->data['Project']['id'], $options);
		
		// Process output
		$this->set('output', 	$log->toHtml());
		if ( $log->hasError() ) {
			$this->set('errorMessage', 	$log->getError() );
			return;
		} 

	}// runAutomatic

	// Ajax steps for manual run -----------------------------
	/**
	 * Ajax controller for the 'export' step of a deployment
	 */
	function export() {
		$this->layout = 'ajax';
		if (!$this->_isValidStep()){
			return;
		} 
		
		// Define options
		$options = array();
		if ($this->data['Project']['revision'] != null) {
			$options['revision'] = $this->data['Project']['revision'];	
		}
		if ($this->data['Project']['user'] != null) {
			$options['user_svn'] = $this->data['Project']['user'];
			$options['password_svn'] = $this->data['Project']['password'];
		}
				
		// Run step 
		$log = $this->Deployment->export($this->data['Project']['id'], $options);
		
		// Process result
		$this->set('output', 	$log->toHtml());
		if ( $log->hasError() ) {
			$this->set('errorMessage', $log->getError() );
			return;
		} 
				
		// Get deployment options for current project
		$options = Configure::read('Deployment.options');
		$projectOptions = $this->Deployment->getConfig();
		if ( isset($projectOptions->options) && is_array($projectOptions->options) ) {
			$options = Set::merge($options, $projectOptions->options);
		}
		$this->set('options', $options); 
		
		// SVN revision
		$revision = !empty($log->data['revision'])?$log->data['revision']:'XXX';
		$this->set('revision', 	$revision);
	}// export

	/**
	 * Ajax controller for the 'synchronize' step of a deployment
	 */
	function synchronize() {
		$this->layout = 'ajax';
		if (!$this->_isValidStep()){
			return;
		} 
		
		// Define options
		$options = array();
		$options['simulation'] 		= ($this->data['Project']['simulation'] == 1);
		$options['runBeforeScript'] = ($this->data['Project']['runBeforeScript'] == 1);
		$options['backup'] 			= ($this->data['Project']['backup'] == 1);
		if ($this->data['DeploymentLog']['comment'] != null) {
			$options['comment'] 	= $this->data['DeploymentLog']['comment'];	
		}
		
		// Run step
		$log = $this->Deployment->synchronize( $this->data['Project']['id'], $options);

		// Process result
		$this->set('output', 	$log->toHtml());
		if ( $log->hasError() ) {
			$this->set('errorMessage', 	$log->getError() );
			return;
		}
	}// synchronize

	/**
	 * Ajax controller for the 'finalize' step of a deployment
	 */	
	function finalize() {
		$this->layout = 'ajax';
		if (!$this->_isValidStep()){
			return;
		} 
						
		// Define options
		$options = array();
		$options['renamePrdFile'] 		= 	($this->data['Project']['RenamePrdFile'] == 1);
		$options['changeFileMode'] 		= 	($this->data['Project']['ChangeFileMode'] == 1);
		$options['giveWriteMode'] 		= 	($this->data['Project']['GiveWriteMode'] == 1);
		$options['runAfterScript']	 	= 	($this->data['Project']['runAfterScript'] == 1);
		
		// Run step
		$this->_setContext($this->data['Project']['id']);	
		$log = $this->Deployment->finalize( $this->data['Project']['id'], $options);

		// Process result
		$this->set('output', 	$log->toHtml());
		if ( $log->hasError() ) {
			$this->set('errorMessage', 	$log->getError() );
			return;
		}
	}// finalize
	
	// On click step -----------------------------------------
	function resetPermissions($id = null){
		$this->layout = 'ajax';
		
		// Run step	
		$log = $this->Deployment->resetPermissions( $id );
		
		// Process result
		$this->set('output', 	$log->toHtml());
		if ( $log->hasError() ) {
			$this->set('errorMessage', 	$log->getError() );
			return;
		}
	}//resetPermissions

	function clearProjectTempFiles($id = null){
		$this->layout = 'ajax';
		// Run step	
		$log = $this->Deployment->clearProjectTempFiles($id);
		
		// Process result
		$this->set('output', 	$log->toHtml());
		if ( $log->hasError() ) {
			$this->set('errorMessage', 	$log->getError() );
			return;
		}
	}// clearProjectTempFiles
	
	// Private helpers ---------------------------------------
	private function _isValidStep() {
		if (!isset($this->data['Project']['id']) || !$this->Session->read('Deployment.uuid')) {  
			$this->set('errorMessage', 	__('Invalid request',true));
			$this->render('error');
			return false;
		} else {
			return true;
		}
	}// _isValidStep
	
	private function _setContext() {		
		if ($this->Session->read('User.User.id')) {
			$user = $this->Session->read('User.User.id');
		} else {
			$user = 'anonymous';
		}
		
		$this->Deployment->setContext(
			array(
				'uuid' 	=> $this->Session->read('Deployment.uuid'),
				'user' 	=> $user
			)
		); 
	}// _setContext
	
}// DeploymentsController
?>