<?php echo $form->labelTag('User/login', '<b>'.LANG_LOGIN.'<span class="req">*</span></b>');?>
<?php echo $html->input('User/login', array('size' => '60', 'class' => 'f-name'));?>
<?php echo $error->showMessage('User/login');?>

<?php echo $form->labelTag('User/first_name', '<b>'.LANG_FIRSTNAME.'</b>');?>
<?php echo $html->input('User/first_name', array('size' => '60', 'class' => 'f-name'));?>
<?php echo $error->showMessage('User/first_name');?>

<?php echo $form->labelTag('User/last_name', '<b>'.LANG_LASTNAME.'</b>');?>
<?php echo $html->input('User/last_name', array('size' => '60', 'class' => 'f-name'));?>
<?php echo $error->showMessage('User/last_name');?>

<?php echo $form->labelTag('User/email', '<b>'.LANG_MAIL.'</b>');?>
<?php echo $html->input('User/email', array('size' => '60', 'class' => 'f-name'));?>
<?php echo $error->showMessage('User/email');?>

<?php echo $html->hidden('User/id')?>

<?php echo $form->labelTag('Group/Group', '<b>'.LANG_ASSOCIATEDGROUPS.'</b>') ?>
<?php echo $html->selectTag('Group/Group', $groups, $selectedGroups, array('multiple' => 'multiple', 'class' => 'f-name selectMultiple'), null, false) ?>
<?php echo $error->showMessage('Group/Group') ?>
