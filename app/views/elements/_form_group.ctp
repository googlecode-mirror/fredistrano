<?php
	$nameErrorMessage = null;
	if (isset($this->validationErrors['Group']) && isset($this->validationErrors['Group']['name'])) {
		($this->validationErrors['Group']['name'] == 'rule1')?
			$nameErrorMessage = __('The name field is required', true):
			$nameErrorMessage = __('This name is already used', true);
	}
?>

<?php echo $form->input('name', array( 
								'label' => '<b>' . __('Name', true). '<span class="req">*</span></b>',
								'size' => '60',
								'class' => 'f-name',
								'error' => $nameErrorMessage
								));
?>
	
<?php echo $form->hidden('id') ?>

<?php echo $form->input('parent_id', array(
									'options' => $groups, 
									'label' => '<b>' . __('Parent', true). '</b>',
									'empty' => '--',
									'class' => 'f-name'
									)); ?>

