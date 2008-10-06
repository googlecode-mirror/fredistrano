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
 * @package			aclite
 * @subpackage		aclite.views.pages
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		aclite
 * @subpackage	aclite.views.pages
 */
 ?>
 <?php 
	echo "<h1>Page d'accueil du plugin AcLitE</h1>";
	echo "Ce plugin permet gérer en toute simplicité vos ACLs.";
	echo "<ul>";
	echo "<li> ".$html->link('Management des ACLs','/aclite/acl_management/index')." </li>";
	echo "<li> ".$html->link('Gestion du plugin aclite','/aclite/slice/index')." </li>";
	echo "</ul><br/><br/><br/><br/>";
?>