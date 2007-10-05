<h1>Resume des permissions en vigueur</h1>

<h2>Liste données de base</h2>

<div style="float: left; width: 45%;">
	<strong>Demandeurs (utilisateurs ou groupes) :</strong>
	<br /><br />
	<?php echo (empty($aros)) ? 'Rien à afficher' : $aros ?>
</div>

<div style="float: right; width: 45%;">
	<strong>Objets de contrôle :</strong>
	<br /><br />
	<?php echo (empty($acos)) ? 'Rien à afficher' : $acos ?>
</div>

<div style="clear: both;"><br /></div>

<h2>Permissions accordées demandeurs et objets de controle <small><?php echo $html->link(' [edit] ','/aclite/aclManagement/updatePermissions') ?></small></h2>

<?php echo $this->renderElement('_displayPermissions') ?>
