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
 * @subpackage		app.views.projects
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.views.projects
 */
 ?>
 <div class="project">
<table class="table1">
<thead>
<tr>
<th colspan="3">
	<span class="tabletoptitle"><?php __('Project details');?></span>
</th>
</tr>
</thead>
<tbody>
<tr>
<th colspan="2">
	<div class="tabletoplink">
		<ul>
			<li><?php echo $ajax->link($html->image('preferences-system.png', 
													array(
														'alt' => __('Reset permissions', true), 
														'title' => __('Reset permissions', true)
														)
													), 
										'/deployments/resetPermissions/' . $project['Project']['id'],
										array (
											'update' 	=> 'deploy_area',
											'loading' 	=> "Element.show('spinning_image0');", 
											'loaded' 	=> "Element.hide('spinning_image0');"
											),
										__('Are you sure you want to reset permissions for the project', true).' : ' . $project['Project']['name'] . '?',
										false
										);?></li>				
			<li><?php echo $ajax->link($html->image('edit-clear.png', 
													array(
														'alt' => __('Delete project temp files', true), 
														'title' => __('Delete project temp files', true)
														)
													), 
										'/deployments/clearProjectTempFiles/' . $project['Project']['id'],
										array (
											'update' 	=> 'deploy_area',
											'loading' 	=> "Element.show('spinning_image0');", 
											'loaded' 	=> "Element.hide('spinning_image0');",
											),
										__('Are you sure you want to delete temp files for the project', true).' : ' . $project['Project']['name'] . '?',
										false
										);?></li>				
			<li><?php echo $html->link($html->image('arrow_switch.png', 
													array(
														'alt' => __('Switch deployment mode (standard / fast)', true), 
														'title' => __('Switch deployment mode (standard / fast)', true)
														)
													), 
										'#',
										array('onclick'=>"Element.toggle('standardDeploy');Element.toggle('fastDeploy')"),
										false,
										false
										);?></li>				
			<li><?php echo $html->link($html->image('date.png', 
													array(
														'alt' => __('View deployment history', true), 
														'title' => __('View deployment history', true)
														)
													), 
										'/deploymentLogs/index/project/' . $project['Project']['id'], 
										null,
										false,
										false);?></li>
			<li>.</li>							
			<li><?php echo $html->link($html->image('b_edit.png', 
													array(
														'alt' => __('Edit', true), 
														'title' => __('Edit', true)
														)
													), 
										'/projects/edit/' . $project['Project']['id'], 
										null,
										false,
										false);?></li>
			<li><?php echo $html->link($html->image('b_drop.png', 
													array(
														'alt' => __('Delete', true), 
														'title' => __('Delete', true)
														)
													), 
										'/projects/delete/' . $project['Project']['id'], 
										null, 
										__('Are you sure you want to delete', true).' : ' . $project['Project']['name'] . '?',
										false );?></li>
		</ul>
	</div>
</th>
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
<td><?php echo $fbollon->logsLinks($project['Project']['log_path'], $project['Project']['id']); ?>&nbsp;</td>
</tr>

<tr>
<th class="sub"><?php __('Deployment method');?></th>
<td>
		<b><?php 
			e($deploymentMethod);
		?></b>
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
		e($ajax->submit(
			__('Deploy', true), 
			array(
				'class' 	=> 'f-submit',
				'url' 		=> '/deployments/runManual/' . $project['Project']['id'],
				'update' 	=> 'deploy_area', 
				'loading' 	=> "Element.show('spinning_image0');", 
				'loaded' 	=> "Element.hide('spinning_image0');",
				'style' 	=> 'float:right;margin:10px',
				'id'		=> 'standardDeploy'
				)
			)
		);
	?>

	<?php 
		e($ajax->submit(
			__('Fast deploy', true), 
			array(
				'class' 	=> 'f-submit',
				'url' 		=> '/deployments/runAutomatic/' . $project['Project']['id'],
				'update' 	=> 'deploy_area', 
				'loading' 	=> "Element.show('spinning_image0');", 
				'loaded' 	=> "Element.hide('spinning_image0');",
				'style' 	=> 'float:right;margin:10px;display:none',
				'id' 		=> 'fastDeploy'
				)
			)
		);
	?>
	<?php e($fbollon->helpButton('help_deploy')) ?>
</form>
<div><?php e($fbollon->displayHelp ('help_deploy', 
									__('You can switch between standard deployment and fast deployment mode by using the switch mode link', true))) ?></div>
</td>
</tr>
</tbody>
</table>

<div class="smalldateblock">
	<?php __('Created on');?> <?php echo $project['Project']['created']?><br />
	<?php __('Modified on');?> <?php echo $project['Project']['modified']?>
</div> 

<div id="deploy_area"></div>

<div id="deploy_result"></div>
<br/><br/>

