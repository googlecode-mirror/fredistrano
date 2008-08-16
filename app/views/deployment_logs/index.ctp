<?php
$project_id = null;
if (isset($this->params['pass'][0]) && $this->params['pass'][0] == 'project') {
	$project_id = $this->params['pass'][1];
}
?>
	<?php echo $form->create('Log', array('url' => '/deploymentLogs', 'method' => 'post', 'class' => 'f-wrap-1'));?>
	<fieldset>
		<h3><?php __('Deployment logs') ?></h3>
		<div>
			<?php echo $form->input('project_id', array(
												'options' 	=> $projects, 
												'label' 	=> '<b>' . __('Project name', true). '</b>',
												'selected' 	=> $project_id,
												'class' 	=> 'f-name',
												'empty'		=> __('All', true)
												)); ?>
			<label for="ProjectSimulation"><b><?php __('Show archived');?></b>
			<?php echo $form->checkbox('showArchived', 
										array(
											'class' => 'f-checkbox'
											)
										); 
			?>									
			</label>	
			<?php e($form->submit('Search',array( 'url' => '/deploymentLogs', 'class' => 'f-submit'))); ?>
		</div>
	</fieldset>
	<?php echo $form->end();?>

<div id="deploymentLogs">
<?php
 	if (!sizeof($logs)) {
 		echo '<em>No logs in database</em><br/><br/><br/><br/>';
 	} else {
 		echo '<ul>';
 		$lastdate = null;
 		foreach($logs as $log) {
 			$created = strtotime($log['DeploymentLog']['created']);
 			$newdate = date("d/m/Y",$created);
 			$com = $log['DeploymentLog']['comment'];
 			$com = $text->truncate($com, 30);
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