<?php 
	echo "<h2>Mise à jour des permissions</h2>";
	echo "<h3>Permissions en vigueur <small>". $html->link(' [view] ','/aclite/aclManagement/listPermissions')."</small></h3>";
	echo $this->renderElement('_displayPermissions');
	echo "<h3>Ajout d'une nouvelle permission</h3>";
?>
<?php echo $form->create('AclManagement', array('method' => 'post', 'class' => 'f-wrap-1', 'url' => '/aclite/aclManagement/updatePermissions')) ?>

	<div id="aclLines">
		<div class="aclLine">
		<?php echo $form->input('Permission/aro', array(
											'options' => $aros, 
											'label' => '',
											'empty' => '--',
											'class' => 'f-name'
											)); ?>
		<?php echo $form->input('Permission/type', array(
											'options' => $types, 
											'label' => '',
											'empty' => '--',
											'class' => 'f-name'
											)); ?>
		exécuter l'action
		<?php echo $form->input('Permission/action', array(
											'options' => $actions, 
											'label' => '',
											'empty' => '--',
											'class' => 'f-name'
											)); ?>
		sur
		<?php echo $form->input('Permission/aco', array(
											'options' => $acos, 
											'label' => '',
											'empty' => '--',
											'class' => 'f-name'
											)); ?>
											
		
		
		
		</div>
	</div>
	<p><br /></p>
	<div class="f-submit-wrap">
		<?php echo $form->submit(__('Add', true), array('class' => 'f-submit'));?>
	</div>
</form>