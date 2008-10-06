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
 * @subpackage		app.views.deployments
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.views.deployments
 */
 ?>
 <?php echo $this->renderElement('_console'); ?>

<script language="JavaScript" type="text/javascript">
<?php 
	e("$('DeploymentLogComment').value='Revision exported $revision';"); 
	if (isset($options['synchronize']['runBeforeScript']) && $options['synchronize']['runBeforeScript']) {
	 	e("$('ProjectRunBeforeScript').checked = 'checked';");
	}
	if (empty($options['synchronize']['runBeforeScript']) && empty($options['synchronize']['backup'])) {
		e("$('ProjectSimulation').checked = 'checked';");
		e("$('ProjectBackup').disabled = 'disabled';");
		e("$('ProjectRunBeforeScript').disabled = 'disabled';");
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