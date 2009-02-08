<?php
/* SVN FILE: $Id$ */
/**
 * Controller that handles request for accessing log files
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
 * Controller that handles request for accessing various log files
 *
 * @package		app
 * @subpackage	app.controllers
 */
uses('file');
class LogsController extends AppController {
	
	var $name = 'Logs';
	
	var $uses = array('Project');
	
	var $helpers = array ('Ajax','Pagination');
	
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
		$projects = $this->Project->find( 
			'list', 
			array(
				'conditions' =>"`Project`.`log_path` != 'NULL'", "`Project`.`log_path` != ''",
				'order'=>'Project.name ASC'
			)
		);
		$this->set('projects', $projects);
		$this->set('logs', $this->getLogList($project_id));
		$this->set('project_id', $project_id);
	}// index
	
	function getLogList($id){
		Configure::write('debug', 0);
		$this->Project->unbindModel( array('hasMany' => array ('DeploymentLog')) );
		$logs = $this->Project->findById($id);
		$logs = explode("\n", $logs['Project']['log_path']);
		$this->set('logs', $logs);
		return $logs;
	}// getLogList
	
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