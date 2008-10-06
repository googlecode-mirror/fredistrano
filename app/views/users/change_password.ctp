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
 * @subpackage		app.views.users
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.views.users
 */
 ?>
 <?php 
$passwordErrorMessage = null;
if (isset($this->validationErrors['User']) && isset($this->validationErrors['User']['password'])) {
	($this->validationErrors['User']['password'] == 'rule1')?
		$passwordErrorMessage = __('The password field is required', true):
		$passwordErrorMessage = __('the password must have a minimal length of 6 characters', true);
}

echo $form->create('User', array('method' => 'post', 'class' => 'f-wrap-1', 'action' => 'change_password/'.$this->params['pass'][0	]));?>
	<div class="req">
		<b>*</b> <?php __('Required fields') ?>
	</div>

	<fieldset>
		<h3><?php __('Change password') ?></h3>
		<?php echo $form->input('old_password', array( 
										'label' => '<b>' . __('Password', true). '<span class="req">*</span></b>',
										'size' => '60',
										'class' => 'f-name', 
										'error' => $passwordErrorMessage, 
										'type' => 'password'
										));
		?>
		<?php echo $form->input('password', array( 
										'label' => '<b>' . __('New password', true). '<span class="req">*</span></b>',
										'size' => '60',
										'class' => 'f-name', 
										'error' => $passwordErrorMessage
										));
		?>
		<?php echo $form->input('confirm_password', array( 
										'label' => '<b>' . __('Confirm new password', true). '<span class="req">*</span></b>',
										'size' => '60',
										'class' => 'f-name', 
										'error' => $passwordErrorMessage, 
										'type' => 'password'
										));
		?>
		
		
		<div class="f-submit-wrap">
			<?php echo $form->submit(__('Save', true), array('class' => 'f-submit'));?>
		</div>
	</fieldset>
<?php echo $form->end();?>