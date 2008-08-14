<?php echo $form->create('User', array('method' => 'post', 'class' => 'f-wrap-1'));?>
	<div class="req">
		<b>*</b> <?php __('Required fields') ?>
	</div>

	<fieldset>
		<h3><?php __('New user') ?></h3>

		<?php echo $this->renderElement('_form_user');?>
		
		<div class="f-submit-wrap">
			<?php echo $form->submit(__('Create', true), array('class' => 'f-submit'));?>
		</div>
	</fieldset>
<?php echo $form->end();?>
