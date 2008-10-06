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
							'error' => __('The SVN Url must be a valid url', true)
							)
						);?>
<?php echo $form->input('Project.prd_path', 
						array(
							'label' => '<b>'.__('Application absolute path', true).'<span class="req">*</span></b>',
							'size' => '60', 
							'class' => 'f-name',
							'error' => __('The application absolute path is required', true)
							)
						);?>
<?php echo $form->input('Project.prd_url', 
						array(
							'label' => '<b>'.__('Application Url', true), 
							'size' => '60', 
							'class' => 'f-name',
							'error' => __('The application url must be a valid url', true)
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

<hr />
						
<?php echo $form->input('Project.method', 
						array(
							'legend' => __('Deployment method', true),
							'label' => false,
							'type' => 'radio',
							'div' => 'z_radio',
							'class' => 'f-radio-wrap',
							'options' => $deploymentMethods
						)
					)?>