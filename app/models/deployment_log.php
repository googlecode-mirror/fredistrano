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

	/** 
	 * Archivage des logs 
	 */
	function archive($timestamp) {
		$conditions = "DeploymentLog.archive = 0 AND DeploymentLog.created <= '".date("Y-m-d",$timestamp)."'";
		$logs = $this->findAll($conditions);
		
		foreach ($logs as $log) {
			// Flag
			$log['DeploymentLog']['archive'] = true;
			$this->save($log);
			
			// Suppresion du fichier
			if ($log['DeploymentLog']['uuid']) {
				$logFile = F_DEPLOYLOGDIR.$log['DeploymentLog']['uuid'].'.log';
				if (file_exists ($logFile)) {
					@unlink( $logFile );
				}
			}//
		}// foreach
		
		return sizeof($logs);
	}// archive
	
	/**
	 * Suppression de tous les logs de la base
	 */
	function delAll() {
		$conditions = null;
		$logs = $this->findAll($conditions, array ('id'));

		foreach ($logs as $log) {
			$this->del($log['DeploymentLog']['id']);
		}
	}// delAll
	
}// DeploymentLog
?>