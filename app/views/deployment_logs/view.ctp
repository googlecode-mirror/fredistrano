<div class="project">
<table class="table1">
<thead>
<tr>

<th colspan="3">
	<span class="tabletoptitle">Deployment log details</span>
</th>

</tr>
</thead>

<tbody>

<tr>
<th class="sub">ID</th>
<td>&nbsp;<?php echo $log['DeploymentLog']['id']?></td>
</tr>

<tr>
<th class="sub"><?php __('Related project', true) ?></th>
<td>&nbsp;<?php echo $html->link($log['Project']['name'],'/projects/view/'.$log['Project']['id'], array('target' => '_blank'), false, true, false)?></td>
</tr>

<tr>
<th class="sub"><?php __('Responsible person', true) ?></th>
<td>&nbsp;<?php echo $log['User']['login']?></td>
</tr>

<tr>
<th class="sub"><?php __('Deployment date', true) ?></th>
<td>&nbsp;<?php  echo $log['DeploymentLog']['created'] ?></td>
</tr>

<tr>
<th class="sub"><?php __('Comment', true) ?></th>
<td>&nbsp;<?php echo nl2br($log['DeploymentLog']['comment']); ?></td>
</tr>

<tr>
<th class="sub"><?php __('Unique identifier', true) ?></th>
<td>&nbsp;<?php echo nl2br($log['DeploymentLog']['uuid']); ?></td>
</tr>

</tbody>
</table>

</div>