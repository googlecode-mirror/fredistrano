<?php
uses('file');
class LogsController extends AppController {
	
	var $name = 'Logs';
	
	var $uses = array('Project');
	
	var $helpers = array (
		'Html',
		'Form',
		'Ajax',
		'Pagination'
	);
	
	var $authLocal = array (
		'Logs'	=> 	array( 'entrance' )
	);
	
	function beforeRender() {
		parent::beforeRender();
		
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array (
			'text' => 'Actions'
		);

		$tab[] = array (
			'text' => 'List projects',
			'link' => '/projects'
		);
		$this->set("context_menu", $tab);
	}
	
	function index($project_id = '') {
		$projects = $this->Project->find( 'list', array("`Project`.`log_path` != 'NULL'", "`Project`.`log_path` != ''"),'Project.name ASC');
		
		$this->set('project_id', $project_id);
		$this->set('projects', $projects);
	}// index
	
	function view() {
		$this->layout = 'ajax';
		if (!empty($this->data) && !empty($this->data['Search']['project_id'])) {
			// Size
			if (!empty($this->data['Search']['maxsize'])) {
				Configure::write('Log.maxSize', $this->data['Search']['maxsize']);
			}
			
			$options = array();
			if (!empty($this->data['Search']['pattern'])) {
				$options['pattern'] = $this->data['Search']['pattern'];
			}
			$options['reverse'] = $this->data['Search']['reverse'];
			$output = $this->Project->readLog($this->data['Search']['project_id'], $options);
			if ( $output === false ) {
				die();
			}

			$this->set('project', 	$this->Project->read(null, $this->data['Search']['project_id']));
			$this->set('log',	 	$output);
			$this->set('size', 		$this->Project->lastReadSize); 
			$this->set('logPath', 	$project['Project']['log_path']);		
		} else {
			die();
		}
	}// view
	
}// Logs
?>