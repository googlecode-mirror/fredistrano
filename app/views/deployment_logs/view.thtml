<div class="project">
<table class="table1">
<thead>
<tr>

<th colspan="3">
	<span class="tabletoptitle">Deployment log details</span>
	<!--
	<span class="tabletoplink">
	<?php echo $ajax->link($html->image( 'arrow_switch.png', array('alt' => 'Déployer le projet', 'title' => 'Déployer le projet')).' Déployer', 
			'/projects/deploy/' . $project['Project']['id'], 
			array('update' => 'deploy_area'),
			null,
			false,
			false);?>
	</span>
	-->
</th>

</tr>
</thead>

<tbody>

<tr>
<th class="sub">ID</th>
<td>&nbsp;<?php echo $log['DeploymentLog']['id']?></td>
</tr>

<tr>
<th class="sub">Related project</th>
<td>&nbsp;<?php echo $html->link($log['Project']['name'],'/projects/view/'.$log['Project']['id'], array('target' => '_blank'), false, true, false)?></td>
</tr>

<tr>
<th class="sub">Responsible person</th>
<td>&nbsp;<?php echo $log['User']['login']?></td>
</tr>

<tr>
<th class="sub">Deployment date</th>
<td>&nbsp;<?php  echo $log['DeploymentLog']['created'] ?></td>
</tr>

<tr>
<th class="sub">Comment</th>
<td>&nbsp;<?php echo nl2br($log['DeploymentLog']['comment']); ?></td>
</tr>

</tbody>
</table>

</div>