<?php echo $form->create('Group', array('method' => 'post', 'class' => 'f-wrap-1'));?>
	<div class="req">
		<b>*</b> <?php __('Required fields') ?>
	</div>

	<fieldset>
		<h3><?php __('Group update') ?></h3>

		<?php echo $this->renderElement('_form_group');?>

		<div class="f-submit-wrap">
			<?php echo $form->submit(__('Update', true), array('class' => 'f-submit'));?>
		</div>
	</fieldset>
<?php echo $form->end();?>

