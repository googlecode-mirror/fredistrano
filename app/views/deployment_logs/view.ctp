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
<td>&nbsp;<?php echo $deployLog['DeploymentLog']['id']?></td>
</tr>

<tr>
<th class="sub"><?php __('Related project') ?></th>
<td>&nbsp;<?php echo $html->link($deployLog['Project']['name'],'/projects/view/'.$deployLog['Project']['id'], array('target' => '_blank'), false, true, false)?></td>
</tr>

<tr>
<th class="sub"><?php __('Responsible person') ?></th>
<td>&nbsp;<?php echo $deployLog['User']['login']?></td>
</tr>

<tr>
<th class="sub"><?php __('Deployment date') ?></th>
<td>&nbsp;<?php  echo $deployLog['DeploymentLog']['created'] ?></td>
</tr>

<tr>
<th class="sub"><?php __('Comment') ?></th>
<td>&nbsp;<?php echo nl2br($deployLog['DeploymentLog']['comment']); ?></td>
</tr>

<!-- 
<tr>
<th class="sub"><?php __('Unique identifier') ?></th>
<td>&nbsp;<?php echo nl2br($deployLog['DeploymentLog']['uuid']); ?></td>
</tr>
-->
</tbody>
</table>
</div>
<br/>

<?php 	e($this->renderElement('_log_viewer')); ?>