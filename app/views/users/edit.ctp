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
 <?php echo $form->create('User', array('method' => 'post', 'class' => 'f-wrap-1'));?>
	<div class="req">
		<b>*</b> <?php __('Required fields') ?>
	</div>

	<fieldset>
		<h3><?php __('User update') ?></h3>

		<?php echo $this->renderElement('_form_user');?>

		<div class="f-submit-wrap">
			<?php echo $form->submit(__('Update', true), array('class' => 'f-submit'));?>
		</div>
	</fieldset>
<?php echo $form->end();?>