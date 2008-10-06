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
 * @subpackage		app.views.projects
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.views.projects
 */
 ?>
 <?php echo $form->create('User', array('url' => '/projects/add', 'method' => 'post', 'class' => 'f-wrap-1'));?>
<div class="req"><b>*</b> <?php __('Required fields');?></div>
    <fieldset>
	    <h3><?php __('New project');?></h3>
	    	<!-- app/views/elements/_form_user.thtml -->
			<?php echo $this->renderElement('_form_project');?>
	    <?php echo $form->submit(__('Save', true), array('class' => 'f-submit')) ?>
	</fieldset>
<?php echo $form->end();?>




