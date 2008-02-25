<hr>
<form action="<?php echo $html->url('/deployments/export'); ?>" method="post" class="f-wrap-1">
	<div class="req">
	</div>
		<fieldset>
			<h3><?php e(LANG_SUBVERSIONEXPORT);?></h3>
		
			<label for="ProjectRevision"><b><?php e(LANG_REVISION);?></b>
			 	<?php echo $html->input('Project/revision', array('size' => '8', 'class' => 'f-name', 'tabindex=1'));?>
			 	<br />
			</label>
			<?php //echo $error->showMessage('Project/revision');?>
			
			<small><a href="#" onclick="Effect.toggle('connectionSettings','slide',{delay: 0.5, duration: 1.5}); return false"><?php e(LANG_SPECIFYSVNLOGIN);?></a></small>
			  <div id="connectionSettings" style="display: none;">
				<label for="ProjectUser"><b><?php e(LANG_USER);?></b>
			 		<?php echo $html->input('Project/user', array('size' => '20', 'class' => 'f-name'));?>
					<br />
				</label>
				<?php //echo $error->showMessage('Project/user');?>
			
				<label for="ProjectPassword"><b><?php e(LANG_PASSWORD);?></b>
			 		<?php echo $html->password('Project/password', array('size' => '20', 'class' => 'f-name'));?>
					<br />
				</label>
			  </div>

			<?php //echo $error->showMessage('Project/password');?>
			
			<?php echo $html->hidden('Project/id', array('value' => $id))?>
	
			<div class="f-submit-wrap">
				<?php echo $ajax->submit(LANG_STEP1, array('class' => 'f-submit','url' => '/deployments/export','update' => 'deploy_result', 'loading' => "Element.show('spinning_image');", 'complete' => "Element.show('step2');", 'loaded' => "Element.hide('spinning_image');"));?>
				<?php e($html->imageTag('loading_orange.gif','Loading...',array('id'=>'spinning_image','style'=>'display:none'))); ?>
			</div>
		</fieldset>
</form>
	
<div id="step2" style="display:none">
	<form action="<?php echo $html->url('/deployments/synchronize'); ?>" method="post" class="f-wrap-1">
			<fieldset>
				<h3><?php e(LANG_SYNCHRONIZINGWITHPRODUCTIONSERVER);?></h3>
				<fieldset class="f-name">
					<fieldset>
				<label for="ProjectSimulation"><b><?php e(LANG_SIMULATION);?></b>
					<!--<b>Simulation</b>-->	<?php e($html->checkbox('Project/simulation', LANG_RSYNCSIMULATION, array(  'onClick' => 'if ($(\'ProjectBackup\').disabled) {$(\'ProjectBackup\').disabled = false; }else{ $(\'ProjectBackup\').disabled = true;  $(\'ProjectBackup\').checked = false;}', 'checked' => 'checked', 'value' => true, 'class' => 'f-checkbox')));	?>
				<br />
				</label>

				<label for="ProjectBackup"><b><?php e('Backup');?></b>
				<?php e($html->checkbox('Project/backup', null, array('disabled' => 'true', 'class' => 'f-checkbox'))); ?>
				<br />
				</label>
					
				<label for="DeploymentLogComment"><b><?php echo LANG_COMMENT; ?></b>
		 		<?php echo $html->textarea('DeploymentLog/comment', array( 'rows' => 5, 'class' => 'f-name'));?>
				<br />
				</fieldset>
			</label>
				</fieldset>
				<?php echo $html->hidden('Project/id', array('value' => $id))?>
				
				<div class="f-submit-wrap">
					<?php echo $ajax->submit(LANG_STEP2, array('class' => 'f-submit','url' => '/deployments/synchronize','update' => 'deploy_result', 'loading' => "Element.show('spinning_image2');", 'complete' => "(document.getElementById('ProjectSimulation').checked)?Element.hide('step3'):Element.show('step3');", 'loaded' => "Element.hide('spinning_image2');"));?>
					<?php e($html->imageTag('loading_orange.gif','Loading...',array('id'=>'spinning_image2','style'=>'display:none'))); ?>
				</div>
			</fieldset>	
	</form>
</div>

<div id="step3" style="display:none">
	<form action="<?php echo $html->url('/deployments/finalize'); ?>" method="post" class="f-wrap-1">
			<fieldset>
				<h3><?php e(LANG_FINALIZATIONOFDEPLOYMENT);?></h3>
				<?php echo $html->hidden('Project/id', array('value' => $id))?>
				
				<fieldset class="f-name">
					<label for="RenamePrdFile"><b><?php e(LANG_RENAMEFILES);?> '.prd.'</b>
						<?php e($html->checkbox('Project/RenamePrdFile', 'Renommage des fichiers .prd.', array((_RENAMEPRDFILE === true)?"'checked' => 'checked'":null, 'value' => true, 'class' => 'f-checkbox')));	?>
					<br />
					</label>
					<label for="ChangeFileMode"><b><?php e(LANG_ADJUSTINGMODES);?></b>
						<?php e($html->checkbox('Project/ChangeFileMode', 'Ajustement des droits', array((_CHANGEMODE === true)?"'checked' => 'checked'":null, 'value' => true, 'class' => 'f-checkbox')));	?>
					<br />
					</label>
					<label for="GiveWriteMode"><b><?php e(LANG_WRITINGMODE);?></b>
						<?php e($html->checkbox('Project/GiveWriteMode', 'Droits d\'écriture', array((_GIVEWRITEMODE === true)?"'checked' => 'checked'":null, 'value' => true, 'class' => 'f-checkbox')));	?>
					<br />
					</label>				
				</fieldset>
				
				<div class="f-submit-wrap">
					<?php echo $ajax->submit(LANG_STEP3, array('class' => 'f-submit','url' => '/deployments/finalize','update' => 'deploy_result', 'loading' => "Element.show('spinning_image3');", 'complete' => "Element.hide('spinning_image3');"));?>
					<?php e($html->imageTag('loading_orange.gif','Loading...',array('id'=>'spinning_image3','style'=>'display:none'))); ?>
				</div>
			</fieldset>	
	</form>
</div>
