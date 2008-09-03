<?php
class DeploymentsController extends AppController {

	var $name = 'Deployments';
	
	var $helpers = array (
		'Html',
		'Form',
		'Ajax',
		'Pagination'
	);

	var $uses = array (
		'Deployment'
	);
	
	var $authLocal = array (
		'Deployments' => array (
			'buinessData'
		)
	);
	
	// Initialize runs ---------------------------------------
	/**
	 * Deploy manually a project
	 * @param string $id ID of the project to be deployed
	 */
	function runManual($id = null) {
		if ($id == null ) {
			$this->Session->setFlash(__('Invalid id.', true));
			$this->redirect('/projects/index');
			exit();
		}
		
		// Give an ID to current deployment 
		$this->Session->write('Deployment.uuid', $this->Deployment->generateUuid($id));
						
		// View
		$this->layout = 'ajax';
		$this->set('id', $id);
	}// runManual
	
	/**
	 * Deploy automatically a project
	 * @param string $id ID of the project to be deployed
	 */
	function runAutomatic($id = null) {
		$this->layout = 'ajax';
		if ($id == null ) {
			$this->Session->setFlash(__('Invalid id.', true));
			$this->redirect('/projects/index');
			exit();
		}

		// Give an ID to current deployment 
		$this->Session->write('Deployment.uuid', $this->Deployment->generateUuid($id));

		// Run step	
		$log = $this->Deployment->process(
			$this->data['Project']['id'], 
			$this->_getContext(), 
			array(
		 		'export' 		=> array(),
		 		'synchronize'	=> array(
					'simulation'			=> 	false,
		 		 	'runBeforeScript'		=> 	false,
		 			'backup'				=> 	false
		 		),
		 		'finalize'		=> array(
			 		'renamePrdFile' 		=> 	false,
					'changeFileMode' 		=> 	false,
					'giveWriteMode'			=> 	false,
		 			'runAfterScript'		=> 	false
		 		)
		 	)
		);

		// Process output
		$this->set('output', 	$log->toString());
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
		$log = $this->Deployment->export($this->data['Project']['id'], $this->_getContext(), $options);
				
		// Process result
		$this->set('output', 	$log->toString());
		if ( $log->hasError() ) {
			$this->set('errorMessage', 	$log->getError() );
			return;
		} 
				
		// Get deployment options for current project
		if ( ($options = $this->Deployment->getConfig()) && is_array($options) ) {
			$defaultOptions = Configure::read('Deployment.options');
			Set::merge($defaultOptions, $options);
		}
		$this->set('options', 	$options); 
		
		// SVN revision
		$revision = ($log->data['revision']!==false)?$log->data['revision']:'XXX';	
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
		$log = $this->Deployment->synchronize( $this->data['Project']['id'], $this->_getContext(), $options);

		// Process result
		$this->set('output', 	$log->toString());
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
		$log = $this->Deployment->finalize( $this->data['Project']['id'], $this->_getContext(), $options);

		// Process result
		$this->set('output', 	$log->toString());
		if ( $log->hasError() ) {
			$this->set('errorMessage', 	$log->getError() );
			return;
		}
	}// finalize
	
	// On click step -----------------------------------------
	function resetPermissions($id = null){
		$this->layout = 'ajax';
		if (!$id) {
			$this->set('errorMessage', 	__('Invalid request',true));
			$this->render('error');
			return;
		}
		
		// Run step	
		$log = $this->Deployment->resetPermissions( $id, $this->_getContext());
		
		// Process result
		$this->set('output', 	$log->toString());
		if ( $log->hasError() ) {
			$this->set('errorMessage', 	$log->getError() );
			return;
		}
	}//resetPermissions
	
	function clearProjectTempFiles($id = null){
		$this->layout = 'ajax';
		if (!$id) {
			$this->set('errorMessage', 	__('Invalid request',true));
			$this->render('error');
			return;
		}
		
		// Run step	
		$log = $this->Deployment->clearProjectTempFiles( $id, $this->_getContext());
		
		// Process result
		$this->set('output', 	$log->toString());
		if ( $log->hasError() ) {
			$this->set('errorMessage', 	$log->getError() );
			return;
		}
	}// clearProjectTempFiles
	
	// Private helpers ---------------------------------------
	private function _getContext() {	
		if ($this->Session->read('User.User.id')) {
			$user = $this->Session->read('User.User.id');
		} else {
			$user = 'anonymous';
		}
		
		return array(
			'uuid' 	=> $this->Session->read('Deployment.uuid'),
			'user' 	=> $user
		);
	}// _getContext
	
	private function _isValidStep() {
		if (!isset($this->data['Project']['id']) || !$this->Session->read('Deployment.uuid')) {  
			$this->set('errorMessage', 	__('Invalid request',true));
			$this->render('error');
			return false;
		} else {
			return true;
		}
	}// _isValidStep
	
}// DeploymentsController
?>