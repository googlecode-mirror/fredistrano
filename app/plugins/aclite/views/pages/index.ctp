<?php 
	echo "<h1>Page d'accueil du plugin AcLitE</h1>";
	echo "Ce plugin permet gérer en toute simplicité vos ACLs.";
	echo "<ul>";
	echo "<li> ".$html->link('Management des ACLs','/aclite/acl_management/index')." </li>";
	echo "<li> ".$html->link('Gestion du plugin aclite','/aclite/slice/index')." </li>";
	echo "</ul><br/><br/><br/><br/>";
?>