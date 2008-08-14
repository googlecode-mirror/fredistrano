<?php
	$loginErrorMessage = null;
	if (isset($this->validationErrors['User']) && isset($this->validationErrors['User']['login'])) {
		($this->validationErrors['User']['login'] == 'rule1')?
			$loginErrorMessage = __('The login field is required', true):
			$loginErrorMessage = __('This login is already used', true);
	}
	
	$passwordErrorMessage = null;
	if (isset($this->validationErrors['User']) && isset($this->validationErrors['User']['password'])) {
		($this->validationErrors['User']['password'] == 'rule1')?
			$passwordErrorMessage = __('The password field is required', true):
			$passwordErrorMessage = __('the password must have a minimal length of 6 characters', true);
	}
	
	
	
?>

<?php echo $form->hidden('id') ?>

<?php echo $form->input('login', array( 
								'label' => '<b>' . __('Login', true). '<span class="req">*</span></b>',
								'size' => '60',
								'class' => 'f-name',
								'error' => $loginErrorMessage
								));
?>
<?php echo $form->input('first_name', array( 
								'label' => '<b>' . __('First name', true). '</b>',
								'size' => '60',
								'class' => 'f-name'
								));
?>
<?php echo $form->input('last_name', array( 
								'label' => '<b>' . __('Last name', true). '</b>',
								'size' => '60',
								'class' => 'f-name'
								));
?>
<?php echo $form->input('email', array( 
								'label' => '<b>' . __('email', true). '<span class="req">*</span></b>',
								'size' => '60',
								'class' => 'f-name', 
								'error' => __('Please supply a valid email address.', true)
								));
?>
	<!-- will be removed when user management will be improved -->
	<?php echo $form->input('password', array( 
									'label' => '<b>' . __('Password', true). '<span class="req">*</span></b>',
									'size' => '60',
									'class' => 'f-name', 
									'error' => $passwordErrorMessage
									));
	?>
