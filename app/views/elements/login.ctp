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
	$loginUrl = Router::url(array('controller'=>'users','action'=>'login'),true);
 	echo $html->image('users.png');
?>
	<a href="#" onclick="Effect.toggle('identificationForm','slide',{delay: 0.5, duration: 1.5}); return false">
		<?php  echo '[' . __('Identification', true) . ']';?>
	</a>

<div id="identificationForm" style="display: none;">
	<?php echo $form->create('User', array('url' => $loginUrl, 'method' => 'post'));?>
	    <fieldset>
		    <?php echo $form->input('login', array('label' => __('Username', true), 'size' => 20, 'class' => 'f-name'));?>
		    <?php echo $form->password('password', array('label' => __('Password', false), 'size' => 20, 'class' => 'f-name'));?>
		    <?php echo $form->submit('login', array('class' => 'f-submit', 'label' => __('Login', true))) ?>
		</fieldset>
	<?php echo $form->end();?>
</div>