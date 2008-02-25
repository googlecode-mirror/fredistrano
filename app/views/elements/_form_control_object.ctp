<?php echo $html->hidden('ControlObject/id')?>

<?php echo $form->labelTag('ControlObject/name', '<b>Nom<span class="req">*</span></b>');?>
<?php echo $html->input('ControlObject/name', array('size' => '60', 'class' => 'f-name'));?>
<?php echo $error->showMessage('ControlObject/name') ?>

<?php echo $form->labelTag('ControlObject/parent_id', '<b>Parent</b>');?>
<?php echo $html->selectTag('ControlObject/parent_id', $controlObjects);?>
<?php echo $error->showMessage('ControlObject/parent_id') ?>
