<?php
/* SVN FILE: $Id$ */
/**
 * 
 * 
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			app
 * @subpackage		app.views.deployment_logs
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.views.deployment_logs
 */
 ?>
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
<td>&nbsp;<?php echo $html->link($deployLog['Project']['name'],'/projects/view/'.$deployLog['Project']['id'])?></td>
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

</tbody>
</table>
</div>
<br/>

<?php
	if (!empty($log)) {
	?>
		<p><strong><?php __('Logs retreived from') ?> <?php e($project['Project']['name']); 
			if (isset($size)) { 
				printf(" [<em>%s</em> - %.3f Ko]", 
					$html->link($logPath, 
						 		'/files/logs/'.$deployLog['DeploymentLog']['uuid'].'.xml',
												null,
												false,
												false)
				
				, ($size/1024));
			} ?></strong></p>

<div class="log_viewer">

		<div style="height: 300px; overflow: auto;">
			<?php e(nl2br(htmlspecialchars($log))); ?>
		</div>	
	<?php 
	} else {
		__('Unable to retreive log');
		echo '<ul><li><i>'.$error.'</i></li></ul>';
	}
	?>
</div>
<br/>