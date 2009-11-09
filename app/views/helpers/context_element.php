<?php
/* SVN FILE: $Id$ */
/**
 * Helper pour l'affichage des context elements
 * 
 *
 * @filesource
 * @link			
 * @package			app
 * @subpackage		app.views.helpers
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 */

/**
 * Class contenant des helpers pour l'affichage des éléments contextuels
 *
 */
class ContextElementHelper extends Helper {
	
	var $helpers = array(
		"Html"
		);
	
	/**
	 * Affiche des liens contextuels (fil d'ariane) 
	 * 
	 * voir le component crumbs pour la génération des données $crumbs
	 *
	 * @param array $crumbs 
	 * @return string
	 */
	function crumbs($crumbs){
		$str = '';
		if (!empty($crumbs)) {
			foreach ($crumbs as $key => $value) {
				$str .= $this->Html->addCrumb($value['name'], $value['link'], $value['options']);
			}
			$str .= $this->Html->getCrumbs('  > ');
		}
		return $str;
	}
	
}
?>