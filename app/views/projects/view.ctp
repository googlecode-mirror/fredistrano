<div class="project">
<table class="table1">
<thead>
<tr>
<th colspan="3">
	<span class="tabletoptitle"><?php __('Project details');?></span>
	<!--
	<span class="tabletoplink">
	<?php echo $ajax->link($html->image( 'arrow_switch.png', array('alt' => __('Deploy', true), 'title' =>  __('Deploy', true))).' '. __('Deploy', true), 
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
<th colspan="2">
	<div class="tabletoplink">
		<ul>					
			<li><?php echo $html->link($html->image( 'date.png', array('alt' => __('View deployment history', true), 'title' => __('View deployment history', true))).' '.__('View deployment history', true), 
					'/deploymentLogs/list_all/project/' . $project['Project']['id'], 
					null,
					false,
					false);?></li>
			<li><?php echo $html->link($html->image( 'b_edit.png', array('alt' => __('Edit', true), 'title' => __('Edit', true))).' '.__('Edit', true), 
					'/projects/edit/' . $project['Project']['id'], 
					null,
					false,
					false);?></li>
			<li><?php echo $html->link($html->image( 'b_drop.png', array('alt' => __('Delete', true), 'title' => __('Delete', true))).' '.__('Delete', true), 
					'/projects/delete/' . $project['Project']['id'], 
					null, 
					__('Are you sure you want to delete', true).' : ' . $project['Project']['name'] . '?',
					false );?></li>
		</ul>
	</div>
</th>
</tr>
<tr>
	<th class="sub"><?php __('Id');?></th>
	<td>&nbsp;<?php echo $project['Project']['id']?></td>
</tr>

<tr>
	<th class="sub"><?php __('Project name');?></th>
	<td>&nbsp;<?php echo $project['Project']['name']?></td>
</tr>

<tr>
	<th class="sub"><?php __('SVN Url');?></th>
	<td>&nbsp;<?php echo $project['Project']['svn_url']?></td>
</tr>

<tr>
	<th class="sub"><?php __('Project absolute path');?></th>
	<td>&nbsp;<?php echo $project['Project']['prd_path']?></td>
</tr>

<tr>
<th class="sub"><?php __('Application Url');?></th>
	<td>&nbsp;<?php echo $html->link($project['Project']['prd_url'], $project['Project']['prd_url'], array('target' => '_blank'), false, true, false) ?></td>
</tr>

<tr>
<th class="sub"><?php __('Log pathes');?></th>
	<td>&nbsp;<?php echo $html->link($project['Project']['log_path'], 
			'/logs/index/' . $project['Project']['id'], 
			null,
			false,
			false);?>
	</td>
</tr>

<tr>
	<th class="sub">&nbsp;</th>
<td>
	
<form action="<?php echo $html->url('/projects/deploy'); ?>" method="post">
<?php echo $form->hidden('Project/id', array('value' => $project['Project']['id']))?>
	<?php e($html->image('loading_orange.gif',
						array(
							'alt' => 'Loading...', 
							'id' => 'spinning_image0',
							'style' => 'display:none'
							)
						)
			); ?>
	<?php 
		echo $ajax->submit(
							__('Deploy', true), 
							array(
								'class' => 'f-submit',
								'url' => '/projects/deploy/' . $project['Project']['id'],
								'update' => 'deploy_area', 
								'loading' => "Element.show('spinning_image0');", 
								'loaded' => "Element.hide('spinning_image0');",
								 'style' => 'float:right;margin:10px'
								)
							);
	?>
	<?php e($ajax->submit(
						__('Fast deploy', true), 
						array(
							'class' => 'f-submit',
							'url' => '/deployments/fastDeploy/' . $project['Project']['id'],
							'update' => 'deploy_area', 
							'loading' => "Element.show('spinning_image0');", 
							'loaded' => "Element.hide('spinning_image0');",
							'style' => 'float:right;margin:10px'
							)
						)
		);
	// TODO Demander une confirmation + affichage
	?>
	<?php e($fbollon->displayHelp ('fast_deploy', 
									__('Use the Fast deploy button to deploy in one click with standart options: svn export, synchronisation and finalization',
									 true
									)
								)
			); 
	?>
	<?php e($fbollon->helpButton('normal_deploy')) ?>
</form>
<div><?php e($fbollon->displayHelp ('normal_deploy', __('Use the deploy button to deploy step by step', true))) ?></div>
</td>
</tr>
</tbody>
</table>

<div class="smalldateblock">
	<?php __('Deploy');?> <?php echo $project['Project']['created']?><br />
	<?php __('Modified on');?> <?php echo $project['Project']['modified']?>
</div> 

<div id="deploy_area"></div>
<div id="deploy_result"></div>

<br/><br/>