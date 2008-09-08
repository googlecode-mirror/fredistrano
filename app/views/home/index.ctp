<h1><?php __('Welcome to Fredistrano') ?></h1>
<p><?php __('Use Fredistrano to deploy your web applications.') ?></p>

<?php if(!empty($_SESSION['User'])): ?>
	<div id='quick_start'>
	<h3>Quick start</h3>
	<ul>
	<li><?php __('Access an existing project: ') ?>
		<?php echo $form->select(
							'Project/id', 
							$projects, 
							null, 
							array('onchange' => "document.location = '".$this->base."/projects/view/'+$('ProjectId').value;"), 
							true); ?>
	</li>
	<li><?php echo $html->link(__('Create a new project', true),'/projects/add'); ?></li>
	</ul>
</div>
<?php endif; ?>

<div id='logsOverview'>
<h3><?php __('Deployment history (10 last)') ?></h3>
<ul>
<?php 
if (empty($logs))
	echo '<li><em>'.__('No logs in database', true).'</em></li>';
else {
	foreach($logs as $log) {
 			$created = strtotime($log['DeploymentLog']['created']);
 			$line = $html->link(date("Y/m/d - H:i:s",$created),'/deploymentLogs/view/'.$log['DeploymentLog']['id']).' : '.__('Deployment of', true).' <b>'.
 				$log['Project']['name'].'</b> by <b>'. $log['User']['login'] . '</b>';
 			echo '<li>'.$line.'</li>';
		}
}
?>
</ul>
</div>