<?php
/* SVN FILE: $Id$ */
/**
 * Composant de navigation contextuel
 * 
 * composant pour la gestion des liens de navigation contextuel
 * actions possibles de navigation 
 *
 * @filesource
 * @link			
 * @package			app.controllers
 * @subpackage		app.controllers.components
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 */

App::import('Component','ContextElements');

/**
* Classe pour la gestion des crumbs (fil d'ariane)
*/
class ContextMenuComponent extends ContextElementsComponent{
	/**
	 * nom du composant
	 *
	 * @var string
	 */
	var $name = 'ContextMenu';
	
	/**
	 * nom de la variable passée à la vue
	 *
	 * @var string
	 */
	var $viewVar = 'contextMenu';
	
	/**
	 * ajoute une section dans la liste des liens
	 *
	 * @param string $name 
	 * @return void
	 */
	function addSection($name){
		$this->addElement($name);
	}
	
}