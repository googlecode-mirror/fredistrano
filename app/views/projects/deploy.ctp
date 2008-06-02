<hr>
<?php echo $form->create('Project', array('url' => '/deployments/export', 'method' => 'post', 'class' => 'f-wrap-1'));?>

	<div class="req">
	</div>
		<fieldset>
			<h3><?php __('Subversion export');?></h3>
 				<?php echo $form->input('Project.revision', array('label' => '<b>'.__('Revision', true).'</b>','size' => '8', 'class' => 'f-name', 'tabindex=1'));?>
			 	<br />
			
			<small><a href="#" onclick="Effect.toggle('connectionSettings','slide',{delay: 0.5, duration: 1.5}); return false"><?php __('Specify SVN login');?></a></small>
			  <div id="connectionSettings" style="display: none;">
 					<?php echo $form->input('Project.user', array('label' => '<b>'.__('User', true).'</b>','size' => '20', 'class' => 'f-name'));?>
					<br />
			
 					<?php echo $form->input('Project.password', array('label' => '<b>'.__('Password', true).'</b>','size' => '20', 'class' => 'f-name'));?>
					<br />
			  </div>
			
			<?php echo $form->hidden('Project.id', array('value' => $id))?>
	
			<div class="f-submit-wrap">
				<?php echo $ajax->submit(__('Step 1 - svn export', true), array('class' => 'f-submit','url' => '/deployments/export','update' => 'deploy_result', 'loading' => "Element.show('spinning_image');", 'complete' => "Element.show('step2');", 'loaded' => "Element.hide('spinning_image');"));?>
				<?php e($html->image('loading_orange.gif',array('alt' => 'Loading...', 'id' => 'spinning_image','style' => 'display:none'))); ?>
			</div>
		</fieldset>
<?php echo $form->end();?>
	
<div id="step2" style="display:none">
	<?php echo $form->create('Project', array('url' => '/deployments/synchronize', 'method' => 'post', 'class' => 'f-wrap-1'));?>
				<h3><?php __('Synchronizing with production server');?></h3>
				<fieldset class="f-name">
					<label for="ProjectSimulation"><b><?php __('Rsync simulation');?></b>
						<?php e($form->checkbox('Project.simulation',  array('class' => 'f-checkbox', 'onClick' => 'if ($(\'ProjectBackup\').disabled) {$(\'ProjectBackup\').disabled = false; }else{ $(\'ProjectBackup\').disabled = true;  $(\'ProjectBackup\').checked = false;}', 'checked' => 'checked', 'value' => true)));	?>
					<br />
					</label>
					<label for="ProjectBackup"><b><?php __('Backup');?></b>	
						<?php e($form->checkbox('Project.backup',  array('class' => 'f-checkbox', 'type' => 'checkbox','disabled' => 'true')));	?>
					<br />
					</label>
						<?php e($form->input('DeploymentLog.comment', array('label' => '<b>'.__('Comment', true).'</b>', 'type' => 'textarea', 'rows' => 5, 'class' => 'f-name')));?>
				 		<?php e($form->hidden('Project.id', array('value' => $id)))?>
				</fieldset>
				<div class="f-submit-wrap">
					<?php e($ajax->submit(__('Step 2 - synchronization', true), array('class' => 'f-submit','url' => '/deployments/synchronize','update' => 'deploy_result', 'loading' => "Element.show('spinning_image2');", 'complete' => "(document.getElementById('ProjectSimulation').checked)?Element.hide('step3'):Element.show('step3');", 'loaded' => "Element.hide('spinning_image2');")));?>
					<?php e($html->image('loading_orange.gif', array('alt' => 'Loading...', 'id'=>'spinning_image2','style'=>'display:none'))); ?>
				</div>
	<?php echo $form->end();?>
</div>

<div id="step3" style="display:none">
	<?php echo $form->create('Project', array('url' => '/deployments/finalize', 'method' => 'post', 'class' => 'f-wrap-1'));?>
			<fieldset>
				<h3><?php __('Finalization of deployment');?></h3>
				<?php echo $html->hidden('Project.id', array('value' => $id))?>
				
				<fieldset class="f-name">
					<label for="RenamePrdFile"><b><?php __('Rename files');?> '.prd.'</b>
						<?php e($html->checkbox('Project.RenamePrdFile', 'Renommage des fichiers .prd.', array((_RENAMEPRDFILE === true)?"'checked' => 'checked'":null, 'value' => true, 'class' => 'f-checkbox')));	?>
					<br />
					</label>
					<label for="ChangeFileMode"><b><?php __('Adjusting modes');?></b>
						<?php e($html->checkbox('Project.ChangeFileMode', 'Ajustement des droits', array((_CHANGEMODE === true)?"'checked' => 'checked'":null, 'value' => true, 'class' => 'f-checkbox')));	?>
					<br />
					</label>
					<label for="GiveWriteMode"><b><?php __('Writing mode');?></b>
						<?php e($html->checkbox('Project.GiveWriteMode', 'Droits d\'écriture', array((_GIVEWRITEMODE === true)?"'checked' => 'checked'":null, 'value' => true, 'class' => 'f-checkbox')));	?>
					<br />
					</label>				
				</fieldset>
				
				<div class="f-submit-wrap">
					<?php e($ajax->submit(__('Step 3 - finalization', true), array('class' => 'f-submit','url' => '/deployments/finalize','update' => 'deploy_result', 'loading' => "Element.show('spinning_image3');", 'complete' => "Element.hide('spinning_image3');")));?>
					<?php e($html->image('loading_orange.gif',array('alt' => 'Loading...', 'id'=>'spinning_image3','style'=>'display:none'))); ?>
				</div>
			</fieldset>	
	<?php echo $form->end();?>
</div>

