<?php
class DeploymentLog extends AppModel {

	var $name = 'DeploymentLog';
	
	var $belongsTo = array (
		'Project' => array (
			'className' => 'Project',
			'foreignKey' => 'project_id'
		),
		'User' => array (
			'className' => 'User',
			'foreignKey' => 'user_id'
		)
	);

	var $feed = null;

	function __construct(){
		parent::__construct();
		
		if (defined('_PUBLISHFEED') && _PUBLISHFEED === true ) {
			$this->feed = array(
			    'titleField'	=> 	'DeploymentLog.title',
			    'descField' 	=> 	'DeploymentLog.comment',
			    'link' 			=> 	'/deploymentLogs/view/%s',
			    'orderby' 		=> 	array('DeploymentLog.created' => 'DESC'),
			    'limit' 		=> 	20
			);
		}
	}
	/** 
	 * Archivage des logs 
	 */
	function archive($timestamp) {
		$conditions = "DeploymentLog.archive = 0 AND DeploymentLog.created <= '".date("Y-m-d",$timestamp)."'";
		$logs = $this->findAll($conditions);
		
		foreach ($logs as $log) {
			$log['DeploymentLog']['archive'] = true;
			$this->save($log);
		}
		
		return sizeof($logs);
	}// archive
	
	/**
	 * Suppression de tous les logs de la base
	 */
	function delAll() {
		$conditions = null;
		$logs = $this->findAll($conditions, array ('id'));

		foreach ($logs as $log)
			$this->del($log['DeploymentLog']['id']);
	}// delAll
	
}// DeploymentLog
?>