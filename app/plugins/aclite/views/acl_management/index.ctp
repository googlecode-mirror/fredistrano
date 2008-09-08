<?php 
	$sure = 'Cette opération entrainera la perte des ACLs actuelles. Souhaitez vous continuer?';
	echo "<h1>Gestion des ACLs</h1>";
	echo "Utiliser cette page pour manger les objets ACL";
	echo "<h2>Administration</h2>";
	echo "<ul>";
	echo "<li> ".$html->link('Suppression des objets existants','/aclite/acl_management/deleteAclObjects',null,$sure)." </li>";
	echo "<li> ".$html->link('Imports des données maîtres en objets ACLs','/aclite/acl_management/importMasterData')." </li>";
	echo "<li> ".$html->link('Réinitialiser les ACL à partir des données de l\'application (User, Group et ControlObject)','/aclite/acl_management/reloadAcls',null,$sure)." </li>";
	echo "</ul>";
	echo "<h2>Utilisation</h2>";
	echo "<ul>";
	echo "<li> ".$html->link('Résumé des permissions actuelles','/aclite/acl_management/listPermissions')." </li>";
	echo "<li> ".$html->link('Faire évoluer les permissions (ajout, suppression)','/aclite/acl_management/updatePermissions')." </li>";
	echo "</ul><br/><br/><br/><br/>";
?>