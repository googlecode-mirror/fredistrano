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
	if (isset($this->validationErrors['ControlObject']) && isset($this->validationErrors['ControlObject']['name'])) {
		($this->validationErrors['ControlObject']['name'] == 'rule1')?
			$nameErrorMessage = __('The name field is required', true):
			$nameErrorMessage = __('This name is already used', true);
	}
?>

<?php echo $form->input('ControlObject.name', array( 
								'label' => '<b>' . __('Name', true). '<span class="req">*</span></b>',
								'size' => '60',
								'class' => 'f-name',
								'error' => $nameErrorMessage
								));
?>
	
<?php echo $form->hidden('ControlObject.id') ?>

<?php echo $form->input('ControlObject.parent_id', array(
									'options' => $controlObjects, 
									'label' => '<b>' . __('Parent', true). '</b>',
									'empty' => '--',
									'class' => 'f-name'
									)); ?>

