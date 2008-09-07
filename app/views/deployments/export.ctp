<?php echo $this->renderElement('_console'); ?>

<script language="JavaScript" type="text/javascript">
<?php 
	e("$('DeploymentLogComment').value='Revision exported $revision';"); 
	if (isset($options['synchronize']['runBeforeScript']) && $options['synchronize']['runBeforeScript']) {
	 	e("$('ProjectRunBeforeScript').checked = 'checked';");
	}
	if (isset($options['synchronize']['backup']) && $options['synchronize']['backup']) {
		e("$('ProjectBackup').checked = 'checked';");
	}
	if (isset($options['finalize']['renamePrdFile']) && $options['finalize']['renamePrdFile']) {
		e("$('ProjectRenamePrdFile').checked = 'checked';");
	}
	if (isset($options['finalize']['changeFileMode']) && $options['finalize']['changeFileMode']) {
		e("$('ProjectChangeFileMode').checked = 'checked';");
	}
	if (isset($options['finalize']['giveWriteMode']) && $options['finalize']['giveWriteMode']) {
		e("$('ProjectGiveWriteMode').checked = 'checked';");
	}
	if (isset($options['finalize']['runAfterScript']) && $options['finalize']['runAfterScript']) {
		e("$('ProjectRunAfterScript').checked = 'checked';");
	}
?>
</script>