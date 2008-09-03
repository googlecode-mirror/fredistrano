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
		$output = $this->Deployment->runProcess(
			$this->data['Project']['id'], 
			$this->_getContext(), 
			array(
		 		'export' 		=> array(),
		 		'synchronize'	=> array(
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
		if ( $output === false ) {
			$this->set('errorMessage', 	$this->Deployment->getLastError());
			$this->render('error');
			return;
		} else {
			$this->set('output', 	$output);
			$this->set('took', 		$this->Deployment->getLastExecutionTime());
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
		$output = $this->Deployment->runStep('export', $this->data['Project']['id'], $this->_getContext(), $options);
				
		// Process output
		if (!$this->_processOutput($output)) {
			return;
		} else {
			// Get deployment options for current project
			$options = $this->Deployment->getConfig();
			$defaultOptions = Configure::read('Deployment.options');
			Set::merge($defaultOptions, $options);
			
			$this->set('options', 	$options); 
			
			/*
				TODO F: SVN pregmatch routine not dry 
			*/
			preg_match('/ ([0-9]+)\.$/', $output, $matches);
			if (isset($matches[1])) {
				$this->set('revision', 	$matches[1]);				
			} else {
				$this->set('revision', 	'XXX');	
			}		
		}
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
		$output = $this->Deployment->runStep('synchronize', $this->data['Project']['id'], $this->_getContext(), $options);
		// Process output
		if (!$this->_processOutput($output)) {
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
		$output = $this->Deployment->runStep('finalize', $this->data['Project']['id'], $this->_getContext(), $options);

		// Process output
		if (!$this->_processOutput($output)) {
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
		$output = $this->Deployment->runStep('resetPermissions', $id, $this->_getContext());
		
		// Process output
		if (!$this->_processOutput($output)) {
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
		$output = $this->Deployment->runStep('clearProjectTempFiles', $id, $this->_getContext());
		
		// Process output
		if (!$this->_processOutput($output)) {
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

	private function _processOutput($output = false) {
		if ( $output === false ) {
			$this->set('errorMessage', 	$this->Deployment->getLastError());
			$this->render('error');
			return false;
		} else {
			$this->set('output', 	$output);
			$this->set('took', 		$this->Deployment->getLastExecutionTime());
			return true;
		}
	}// _processOutput
	
	// function test(){
	// 	debug($this->Deployment->dirMode());
	// 	exit;
	// }
	
}// DeploymentsController
?>