<?php
/* SVN FILE: $Id$ */
/**
 * Composant crumbs (fil d'ariane)
 * 
 * composant pour la gestion des fils d'ariane
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
class CrumbsComponent extends ContextElementsComponent{
	/**
	 * nom du composant
	 *
	 * @var string
	 */
	var $name = 'Crumbs';
	
	/**
	 * nom de la variable passée à la vue
	 *
	 * @var string
	 */
	var $viewVar = 'crumbs';
	
	/**
	 * lien racine du fil d'ariane
	 *
	 * @var string
	 */
	var $root = array();
	
	/**
	 * nom du dernier élément du fil d'ariane
	 *
	 * @var string
	 */
	var $leaf = null;

	/**
	 * Initialisation du composant
	 *
	 * @param Controller $controller 
	 * @return void
	 */
	function startup(&$controller) {
		parent::startup($controller);
		
		$defaultRoot = array(
			'name' => __('Home', true),
			'link' => '/',
			'options' => null 
			);
		$root = array_merge($defaultRoot, $this->root);
		
		$this->addLink($root['name'], $root['link'], $root['options']);
    }

	function beforeRender(&$controller){
		$this->addElement($this->leaf);
		parent::beforeRender($controller);
	}

}