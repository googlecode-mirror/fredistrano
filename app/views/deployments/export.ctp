<div class="console">
<?php
	echo "console [executed in $took s] >";
	echo "<br>";
	echo "<pre>";
	print_r($output);
	echo "</pre>";
?> 
</div>

<script language="JavaScript" type="text/javascript">
  $('DeploymentLogComment').value = "Revision exported <?php echo $revision;?>";
<?php 
	if (isset($options['synchronize']['runBeforeScript']) && $options['synchronize']['runBeforeScript']) {
		e("$('ProjectRunBeforeScript').checked = 'checked'");
	} 
	if (isset($options['synchronize']['backup']) && $options['synchronize']['backup']) {
		e("$('ProjectBackup').checked = 'checked'");
	}
	if (isset($options['finalize']['renamePrdFile']) && $options['finalize']['renamePrdFile']) {
		e("$('ProjectRenamePrdFile').checked = 'checked'");
	}
	if (isset($options['finalize']['changeFileMode']) && $options['finalize']['changeFileMode']) {
		e("$('ProjectChangeFileMode').checked = 'checked'");
	}
	if (isset($options['finalize']['giveWriteMode']) && $options['finalize']['giveWriteMode']) {
		e("$('ProjectGiveWriteMode').checked = 'checked'");
	}
	if (isset($options['finalize']['runAfterScript']) && $options['finalize']['runAfterScript']) {
		e("$('ProjectRunAfterScript').checked = 'checked'");
	}
?>
</script>