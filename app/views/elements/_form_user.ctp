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
 * @subpackage		app.views.elements
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.views.elements
 */
 ?>
 <?php
	$loginErrorMessage = null;
	if (isset($this->validationErrors['User']) && isset($this->validationErrors['User']['login'])) {
		($this->validationErrors['User']['login'] == 'rule1')?
			$loginErrorMessage = __('The login field is required and must be alphanumeric', true):
			$loginErrorMessage = __('This login is already used', true);
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
								'label' => '<b>' . __('email', true). '</b>',
								'size' => '60',
								'class' => 'f-name'
								));
?>
	<!-- will be removed when user management will be improved -->
	<?php echo $form->input('password', array( 
									'label' => '<b>' . __('Password', true). '<span class="req">*</span></b>',
									'size' => '60',
									'class' => 'f-name', 
									'error' => __('the password must have a minimal length of 4 characters', true)
									));
	?>
