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
			'text' => __('Project list', true),
			'link' => '/projects'
		);
		$this->set("context_menu", $tab);
	}
	
	function index($project_id = '') {
		$projects = $this->Project->find( 'list', array("`Project`.`log_path` != 'NULL'", "`Project`.`log_path` != ''"),'Project.name ASC');
		$this->set('logs', $this->getLogList($project_id));
		$this->set('project_id', $project_id);
		$this->set('projects', $projects);
	}// index
	
	function getLogList($id){
		Configure::write('debug', 0);
		$this->Project->unbindModel( array('hasMany' => array ('DeploymentLog')) );
		$logs = $this->Project->findById($id);
		$logs = explode("\n", $logs['Project']['log_path']);
		$this->set('logs', $logs);
		return $logs;
	}
	
	function view() {
		$this->layout = 'ajax';
		if (!empty($this->data) && !empty($this->data['Log']['project_id']) && !is_null($this->data['Search']['logPath'])) {
			// Size
			if (!empty($this->data['Search']['maxsize'])) {
				Configure::write('Log.maxSize', $this->data['Search']['maxsize']);
			}
			
			$project = $this->Project->read(null, $this->data['Log']['project_id']);
			$logfiles = explode("\n",$project['Project']['log_path']);
			
			$options = array();
			if (!empty($this->data['Search']['pattern'])) {
				$options['pattern'] = $this->data['Search']['pattern'];
			}
			$options['logPath'] = trim($logfiles[$this->data['Search']['logPath']]);
			$options['reverse'] = $this->data['Search']['reverse'];
			$output = $this->Project->readAssociatedLog($this->data['Log']['project_id'], $options);
			if ( $output === false ) {
				$this->set('error', 	$this->Project->lastReadError);
			} else {
				$this->set('project', 	$project);
				$this->set('size', 		$this->Project->lastReadSize); 
				$this->set('logPath',	$options['logPath']);	
			}
			$this->set('log', $output);
		}  else {
			$this->set('log',		false);
			$this->set('error', 	__('Invalid request', true));
		}
	}// view
	
}// Logs
?>