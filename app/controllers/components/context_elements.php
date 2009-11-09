<?php
/* SVN FILE: $Id$ */
/**
 * Composant context element 
 * 
 * composant pour la gestion des elements contextuels
 *
 * @filesource
 * @link			
 * @package			app.controllers
 * @subpackage		app.controllers.components
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 */
/**
* Classe pour la gestion des elements contextuels
*/
abstract class ContextElementsComponent extends Object{
	/**
	 * nom du composant
	 *
	 * @var string
	 */
	var $name = 'ContextElements';
	
	/**
	 * nom de la variable passée à la vue
	 *
	 * @var string
	 */
	var $viewVar = 'list';
    
	/**
	 * tableau de liste de lien
	 *
	 * @var array
	 */
	var $list = array();
	
	/**
	 * Initialisation du composant
	 *
	 * @param Controller $controller 
	 * @return void
	 */
	function startup(&$controller) {
    }

	/**
	 * ajoute un élément dans la liste des liens
	 *
	 * @param string $name 
	 * @param string $link 
	 * @param array $options 
	 * @return void
	 */
	function addElement($name, $options=null){
		array_push($this->list, array(
			'name' => $name,
			'link' => null, // pour eviter les erreurs dans les helpers
			'options' => $options
		));
	}
	
	/**
	 * ajoute un lien dans la liste des liens
	 *
	 * @param string $name 
	 * @param string $link 
	 * @param string $options 
	 * @return void
	 */
	function addLink($name, $link=null, $options=null){
		array_push($this->list, array(
			'name' => $name,
			'link' => $link,
			'options' => $options
		));
	}
	
	/**
	 * retourne la liste des liens
	 *
	 * @return array
	 */
	function getList(){
		return $this->list;
	}
	
	function beforeRender(&$controller){
		//Passage à la vue du tableau de lien
		$controller->set($this->viewVar, $this->getList());
	}
	
	function debug() {
		print_r($this->list);
	}
	
}