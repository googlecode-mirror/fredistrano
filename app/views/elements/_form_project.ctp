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
	$nameErrorMessage = null;
	if (isset($this->validationErrors['Project']) && isset($this->validationErrors['Project']['name'])) {
		($this->validationErrors['Project']['name'] == 'rule1')?
			$nameErrorMessage = __('The name field is required and must be alphaNumeric', true):
			$nameErrorMessage = __('This name is already used', true);
	}
?>


<?php echo $form->input('Project.name', 
						array(
							'label' => '<b>'.__('Project name', true).'<span class="req">*</span></b>',
							'size' => '60', 
							'class' => 'f-name',
							'error' => $nameErrorMessage
							)
						);?>
<?php echo $form->input('Project.svn_url', 
						array(
							'label' => '<b>'.__('SVN Url', true).'<span class="req">*</span></b>', 
							'size' => '60', 
							'class' => 'f-name',
							'error' => __('This field is required', true)
							)
						);?>
<?php echo $form->input('Project.prd_path', 
						array(
							'label' => '<b>'.__('Application absolute path', true).'<span class="req">*</span></b>',
							'size' => '60', 
							'class' => 'f-name',
							'error' => __('This field is required', true)
							)
						);?>
<?php echo $form->input('Project.prd_url', 
						array(
							'label' => '<b>'.__('Application Url', true).'</b>', 
							'size' => '60', 
							'class' => 'f-name'
							)
						);?>
<?php echo $form->input('Project.log_path', 
						array(
							'label' => '<b>'.__('Log pathes (one per line)', true).'</b>', 
							'cols' => '70', 
							'class' => 'f-name',
							'type' => 'textarea'
							)
						);?>
						
<label for='Project.method'><b><?php __('Checkout source code') ?></b></label>
<?php 
	e($form->checkbox('Project.method').'&nbsp;').
		__('If checked, Fredistrano downloads only code updates (faster method); otherwise, it performs a "SVN export".');
?>