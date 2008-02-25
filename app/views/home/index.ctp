<h1>Welcome to Fredistrano</h1>
<p>Use Fredistrano to deploy your web applications.</p>

<?php if(!empty($_SESSION['User'])): ?>
	<div id='quick_start'>
	<h3>Quick start</h3>
	<ul>
	<li>Access an existing project: <?php echo $form->select('Project/id', $projects, null, array('onchange' => "document.location = '".$this->base."/projects/view/'+$('ProjectId').value;"), null, true); ?></li>
	<li><?php echo $html->link('Create a new project','/projects/add'); ?></li>
	</ul>
</div>
<?php endif; ?>

<div id='logsOverview'>
<h3>Deployment history (10 last)</h3>
<ul>
<?php 
if (empty($logs))
	echo '<li><em>No logs in database</em></li>';
else {
	foreach($logs as $log) {
 			$created = strtotime($log['DeploymentLog']['created']);
 			$line = date("m/d/Y - H:i:s",$created).' : Deployment of <b>'.
 				$log['Project']['name'].'</b> by <b>'. $log['User']['login'] . '</b>';
 			echo '<li>'.$line.'</li>';
		}
}
?>
</ul>
</div>