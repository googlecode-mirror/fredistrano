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
 * @subpackage		app.views.elements
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.views.elements
 */
 ?>
<?php
$user = $session->read('User');
echo "<center><p>".__('Welcome', true)." ".$user['User']['login']." ";
echo "<br />[ ".$html->link(__('Settings', true), '/users/settings/'.$user['User']['id'])."  -  ".$html->link(__('Logout', true),'/users/logout')." ]</p></center>";
?>