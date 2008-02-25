<div class="deploymentLogs">
<h2>Deployment logs</h2>

<?php 
if (!isset($this->params['url']['showArchived'])){
	echo $html->link('[All logs]','/deploymentLogs/list_all?showArchived' ); 
}else{
	echo $html->link('[Ignore archived logs]','/deploymentLogs/list_all' ); 
}
?><br/>
<?php

 	if (!sizeof($logs)) {
 		echo '<em>No logs in database</em><br/><br/><br/><br/>';
 	} else {
 		if (!empty($filter)) {
 			echo 'Results filtered by : ';
 			if (isset($filter['person'])) {
 				echo 'person|<b>' . $filter['person'].'</b> ';
 			}
 			if (isset($filter['project'])) {
 				echo 'project|<b>' . $filter['project'].'</b>';
 			}
 			echo ' '.$html->link('[X]','/deploymentLogs/list_all' ).'';
 		}
 		echo '<ul>';
 		$lastdate = null;
 		foreach($logs as $log) {
 			$created = strtotime($log['DeploymentLog']['created']);
 			$newdate = date("d/m/Y",$created);
 			$com = $log['DeploymentLog']['comment'];
 			$com = (strlen($com) > 20 )? substr($com, 0, 20).'...':$com;
 			if ($newdate != $lastdate) {
 				$lastdate = $newdate;
 				echo '<h3>'.$newdate.'</h3>';
 			}
 			$line = $html->link(date("H:i:s",$created),'/deploymentLogs/view/'.$log['DeploymentLog']['id']).' : deployment of <b>'.
 				$log['Project']['name'].'</b> by <b>'. $log['User']['login'].'</b> [<i>'. $com .'</i>]';
 			echo '<li>'.$line.'</li>';
		}
		echo '</ul>';
	}

?>
</div>