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
	if (isset($context_menu)){
		echo "<ul id='nav-secondary'>";
	
		for ($i = 0; $i < sizeof($context_menu); $i++) {

			echo "<li class='first'>";

			//affichage des liens Ajax
			if (isset($context_menu[$i]['divid'])) {
				echo $ajax->link($context_menu[$i]['text'],$context_menu[$i]['link'],array('update' => $context_menu[$i]['divid']));
			//affichage des categories
			} else if (!isset($context_menu [$i]['link'])) {
				echo '<h6>'.$context_menu [$i]['text'].'</h6>';

			//affichage des liens normaux
			} else {
				$confirm = false;
				if (isset($context_menu[$i]['confirm']))
					$confirm = $context_menu[$i]['confirm'];

				echo $html->link($context_menu[$i]['text'], $context_menu[$i]['link'], null, $confirm );
			}// if
			echo "</li>";
		}// for
		echo "</ul>";
	}//if
?>