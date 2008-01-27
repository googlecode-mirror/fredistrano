<?php
class DeploymentsController extends AppController {

	var $name = 'Deployments';
	
	var $helpers = array (
		'Html',
		'Form',
		'Ajax',
		'Pagination',
		'Error'
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
		if (!isset($this->data['Project']['id'])) {  
			$this->set('errorMessage', 	$this->Deployment->getLastError());
			$this->render('error');
			exit();
		}
		
		$this->layout = 'ajax';

		$options = array();
		if ($this->data['Project']['id'] != null) $options['revision'] = $this->data['Project']['revision'];
		if ($this->data['Project']['user'] != null) {
			$options['user'] = $this->data['Project']['user'];
			$options['password'] = $this->data['Project']['password'];
		}

		$output = $this->Deployment->runStep('export', $this->data['Project']['id'], $options);

		if ( $output === false ) {
			$this->set('errorMessage', 	$this->Deployment->getLastError());
			$this->render('error');
			exit();
		} else {
			preg_match('/ ([0-9]+)\.$/', $output, $matches);
			
			if (isset($matches[1]))
				$this->set('revision', 	$matches[1]);
			else 
				$this->set('revision', 	'XXX');
			$this->set('output', 	$output);
			$this->set('took', 		$this->Deployment->getLastExecutionTime());
		}
	}// export

	/**
	 * Ajax controller for the 'synchronize' step of a deployment
	 */
	function synchronize() {
		if (!isset($this->data['Project']['id'])) {  
			$this->set('errorMessage', 	$this->Deployment->getLastError());
			$this->render('error');
			exit();
		}
		
		$this->layout = 'ajax';

		$options = array();
		$options['simulation'] = ($this->data['Project']['simulation'] == 1);
		if ($this->data['DeploymentLog']['comment'] != null) $options['comment'] = $this->data['DeploymentLog']['comment'];
				
		$output = $this->Deployment->runStep('synchronize', $this->data['Project']['id'], $options);

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
		if (!isset($this->data['Project']['id'])) {  
			$this->set('errorMessage', 	$this->Deployment->getLastError());
			$this->render('error');
			exit();
		}
				
		$this->layout = 'ajax';

		$options = array();
		$options['renamePrdFile'] 	= 	($this->data['Project']['RenamePrdFile'] == 1);
		$options['changeFileMode'] 	= 	($this->data['Project']['ChangeFileMode'] == 1);
		$options['giveWriteMode'] 	= 	($this->data['Project']['GiveWriteMode'] == 1);
				
		$output = $this->Deployment->runStep('finalize', $this->data['Project']['id'], $options);

		if ( $output === false ) {
			$this->set('errorMessage', 	$this->Deployment->getLastError());
			$this->render('error');
			exit();
		} else {
			$this->set('output', 	$output);
			$this->set('took', 		$this->Deployment->getLastExecutionTime());
		}
	}// finalize
	
}// DeploymentsController
?>