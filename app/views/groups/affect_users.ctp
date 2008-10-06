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
 * @subpackage		app.views.groups
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.views.groups
 */
 ?>
 <?php echo $javascript->link('multipleSelectBox.js');?>

<?php echo $form->create('Group', array(
									'method' => 'post', 
									'class' => 'f-wrap-1',
									'onsubmit' => 'multipleSelectOnSubmit()',
									'action' => 'affectUsers/'.$group['Group']['id']
									)
						);?>
	<fieldset>
		<h3><?php __('Manage group members for group : ') ?><?php echo strtoupper($group['Group']['name'])?></h3>

		<p><?php echo $html->link('[' . __('Display group details', true) . ']', '/groups/view/'.$group['Group']['id']) ?></p>

		<p><?php echo sprintf(__('Manage users members of group %s by using buttons or by double-clicking on usernames', true), 
				'<strong>' . strtoupper($group['Group']['name']) . '</strong>');?></p>

		<select multiple name="fromBox" id="fromBox">
			<?php foreach ($personList as $key => $value) {
				e('<option value="'.$key.'">'.$value.'</option>');
			}?>
		</select>
	
		<select multiple name="data[User][]" id="toBox">
			<?php 
			debug($members);
			
			foreach ($members as $id => $login) {
				e('<option value="'.$id.'">'.$login.'</option>');
			} 
			?>
		</select>

	</fieldset>
		<?php echo $form->hidden('Group/id',array('value'=>$group['Group']['id'])) ?>

		<div class="f-submit-wrap">
			<?php echo $form->submit(__('Update', true), array('class' => 'f-submit'));?>
		</div>
		
<?php echo $form->end();?>

<script type="text/javascript">
createMovableOptions("fromBox","toBox",500,300,'<?php __('LDAP Users') ?>','<?php __('Members of group ') ?><?php echo strtoupper($group['Group']['name'])?>');
</script>

