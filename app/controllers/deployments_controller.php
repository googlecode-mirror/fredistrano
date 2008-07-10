<?php

if (!class_exists('File')) {
	 uses('file');
}

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
		
//	/**
//	 * Ajax controller for initializing a project
//	 */
//	function initialize() {
//	}// initialize

	/**
	 * Ajax controller for the 'export' step of a deployment
	 */
	function export() {
		$this->layout = 'ajax';
		if (!isset($this->data['Project']['id']) || !($uuid = $this->Session->read('Deployment.uuid'))) {
			$this->set('errorMessage', 	'Invalid request');
			$this->render('error');
			exit();
		}
		
		// Define options
		$options = array();
		if ($this->data['Project']['revision'] != null) {
			$options['revision'] = $this->data['Project']['revision'];	
		}
		if ($this->data['Project']['user'] != null) {
			$options['user'] = $this->data['Project']['user'];
			$options['password'] = $this->data['Project']['password'];
		}
		
		// Run step
		$output = $this->Deployment->runStep('export', $this->data['Project']['id'], $uuid, $options);
				
		// Process output
		if ( $output === false ) {
			$this->set('errorMessage', 	$this->Deployment->getLastError());
			$this->render('error');
			exit();
		} else {
			preg_match('/ ([0-9]+)\.$/', $output, $matches);
			
			if (isset($matches[1])) {
				$this->set('revision', 	$matches[1]);				
			} else {
				$this->set('revision', 	'XXX');	
			}
			$this->set('output', 	$output);
			$this->set('took', 		$this->Deployment->getLastExecutionTime());
		}
	}// export

	/**
	 * Ajax controller for the 'synchronize' step of a deployment
	 */
	function synchronize() {
		$this->layout = 'ajax';
		if (!isset($this->data['Project']['id']) || !($uuid = $this->Session->read('Deployment.uuid'))) {
			$this->set('errorMessage', 	'Invalid request');
			$this->render('error');
			exit();
		}
		
		// Define options
		$options = array();
		$options['uuid'] = $uuid;
		$options['simulation'] 	= ($this->data['Project']['simulation'] == 1);
		$options['backup'] 		= ($this->data['Project']['backup'] == 1);
		if ($this->data['DeploymentLog']['comment'] != null) {
			$options['comment'] = $this->data['DeploymentLog']['comment'];	
		}		
		if ($this->Session->read('User.id')) {
			$options['user'] = $this->Session->read('User.id');
		}
		
		// Run step
		$output = $this->Deployment->runStep('synchronize', $this->data['Project']['id'], $uuid, $options);

		// Process output
		if ( $output === false ) {
			$this->set('errorMessage', 	$this->Deployment->getLastError());
			$this->render('error');
			exit();
		} else {
			$this->set('output', 	$output);
			$this->set('took', 		$this->Deployment->getLastExecutionTime());
		}
	}// synchronize

	/**
	 * Ajax controller for the 'finalize' step of a deployment
	 */	
	function finalize() {
		$this->layout = 'ajax';
		if (!isset($this->data['Project']['id']) || !($uuid = $this->Session->read('Deployment.uuid'))) {  
			$this->set('errorMessage', 	'Invalid request');
			$this->render('error');
			exit();
		}
						
		// Define options
		$options = array();
		$options['renamePrdFile'] 	= 	($this->data['Project']['RenamePrdFile'] == 1);
		$options['changeFileMode'] 	= 	($this->data['Project']['ChangeFileMode'] == 1);
		$options['giveWriteMode'] 	= 	($this->data['Project']['GiveWriteMode'] == 1);
			
		// Run step	
		$output = $this->Deployment->runStep('finalize', $this->data['Project']['id'], $uuid, $options);

		// Process output
		if ( $output === false ) {
			$this->set('errorMessage', 	$this->Deployment->getLastError());
			$this->render('error');
			exit();
		} else {
			$this->set('output', 	$output);
			$this->set('took', 		$this->Deployment->getLastExecutionTime());
		}
	}// finalize
	
	function fastDeploy($id = null) {
		$this->layout = 'ajax';
		if ($id == null ) {
			$this->Session->setFlash(__('Invalid id.', true));
			$this->redirect('/projects/index');
			exit();
		}

		// Give an ID to current deployment 
		$deploymentUuid = md5( 'YLB:'.$id .':'.time() ); 

		// Run step	
		$output = $this->Deployment->runProcess(
												$this->data['Project']['id'], 
												$deploymentUuid, 
												array(
													'backup'			=>	false,
													'simulation' 		=> 	false,
													'renamePrdFile' 	=> 	true,
													'changeFileMode' 	=> 	true,
													'giveWriteMode'		=> 	true
													)
												);

		// Process output
		if ( $output === false ) {
			$this->set('errorMessage', 	$this->Deployment->getLastError());
			$this->render('error');
			exit();
		} else {
			$this->set('output', 	$output);
			$this->set('took', 		$this->Deployment->getLastExecutionTime());
		}
	}// fastDeploy
	
}// DeploymentsController
?>