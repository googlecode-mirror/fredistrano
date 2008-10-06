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
 * @subpackage		aclite.views.elements
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		aclite
 * @subpackage	aclite.views.elements
 */
 ?>
 <div>
<?php 
	if (empty($permissions))
		echo "Aucune permission enregistrée";
	else {
		echo '<ul>';
		foreach($permissions as $permission) {
			$allowed = '';
			$denied = '';
			$nothing = 0;
			
			if ($permission['Permission']['_create'] == 1)
				$allowed .= 'create ';
			else if ($permission['Permission']['_create'] == -1)
				$denied .= 'create '; 
			else 
				$nothing ++;

			if ($permission['Permission']['_delete'] == 1)
				$allowed .= 'delete ';
			else if ($permission['Permission']['_delete'] == -1)
				$denied .= 'delete ';
			else 
				$nothing ++;
					
			if ($permission['Permission']['_update'] == 1)
				$allowed .= 'update ';
			else if ($permission['Permission']['_update'] == -1)
				$denied .= 'update ';
			else 
				$nothing ++;
					
			if ($permission['Permission']['_read'] == 1)
				$allowed .= 'read ';
			else if ($permission['Permission']['_read'] == -1)
				$denied .= 'read ';
			else 
				$nothing ++;
				
			$delete = $edit?$html->link(' [X] ','/aclite/aclManagement/deletePermission/'.$permission['Permission']['id'],null,'Supprimer cette permission?'):'';

			if (strlen($denied)>0 && strlen($allowed)>0 && $nothing > 0) 
				echo '<li><b>'.$permission['Aro']['alias'].'</b> peut effectuer sur <b>'.$permission['Aco']['alias'].'</b> les actions suivantes [<i>'.$allowed.
					'</i> ] mais ne peut pas effectuer [ '.$denied.'] '.$delete.'</li>';
				
			else if (strlen($allowed)>0 && $nothing > 0) 
				echo '<li><b>'.$permission['Aro']['alias'].'</b> peut effectuer sur <b>'.$permission['Aco']['alias'].'</b> les actions suivantes [<i>'.$allowed.
					'</i> ] '.$delete.'</li>';
				
			else if (strlen($denied)>0 && $nothing > 0) 
				echo '<li><b>'.$permission['Aro']['alias'].'</b>  ne peut pas effectuer [ '.$denied.'] '.$delete.'</li>';
				
			else if (strlen($denied) == 0) 
				echo '<li><b>'.$permission['Aro']['alias'].'</b> peut <b>tout</b> faire sur <b>'.$permission['Aco']['alias'].'</b> '.$delete.'</li>';

			else if (strlen($allowed) == 0) 
				echo '<li><b>'.$permission['Aro']['alias'].'</b> ne peut <b>rien</b> faire sur <b>'.$permission['Aco']['alias'].'</b> '.$delete.'</li>';
			
		}
		echo '</ul>';
	}
?>
</div>
