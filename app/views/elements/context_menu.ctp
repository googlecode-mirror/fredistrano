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
	if (isset($contextMenu)){
		echo "<ul id='nav-secondary'>";
	
		for ($i = 0; $i < sizeof($contextMenu); $i++) {

			echo "<li class='first'>";

			//affichage des liens Ajax
			if (isset($contextMenu[$i]['divid'])) {
				echo $ajax->link($contextMenu[$i]['name'],$contextMenu[$i]['link'],array('update' => $contextMenu[$i]['divid']));
			//affichage des categories
			} else if (!isset($contextMenu [$i]['link'])) {
				echo '<h6>'.$contextMenu [$i]['name'].'</h6>';

			//affichage des liens normaux
			} else {
				$confirm = false;
				if (isset($contextMenu[$i]['confirm']))
					$confirm = $contextMenu[$i]['confirm'];

				echo $html->link($contextMenu[$i]['name'], $contextMenu[$i]['link'], null, $confirm );
			}// if
			echo "</li>";
		}// for
		echo "</ul>";
	}//if
?>