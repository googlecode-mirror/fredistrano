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
 * @subpackage		aclite.views
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		aclite
 * @subpackage	aclite.views
 */
 ?>
<h1><?php __('Current permissions summary') ?></h1>

<h2><?php __('Master data') ?></h2>

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
