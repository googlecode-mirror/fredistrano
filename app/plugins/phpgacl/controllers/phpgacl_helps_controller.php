<?php

/**
 * PhpgaclHelps Controller class file.
 *
 * Controller for PhpgaclHelps actions.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Sypad, Inc.
 * @link			http://www.sypad.com SYPAD
 * @package			sypad
 * @subpackage		sypad.plugins.phpgacl.controllers
 * @since			Sypad v 1.0
 */

/**
 * Shows help pages.
 *
 * @author  Mariano Iglesias
 * @package sypad
 * @subpackage sypad.plugins.phpgacl.controllers
 * @since   1.0
 */
class PhpgaclHelpsController extends PhpgaclAppController
{
	/**#@+
	 * @access protected
	 */
	/**
	 * Name of the controller.
	 *
	 * @var string
	 * @since 1.0
	 */
	var $name = 'PhpgaclHelps';
	
	/**
	 * Helpers used by this controller.
	 *
	 * @var array
	 * @since 1.0
	 */
	var $helpers = array( 'Javascript', 'PhpgaclHtml' );
	
	/**
	 * Models used by this controller.
	 *
	 * @var array
	 * @since 1.0
	 */
	var $uses = null;
	/**#@-*/
	
	/**
	 * Runs after the action has been executed, and before the view is rendered.
	 *
	 * @access protected
	 * @since 1.0
	 */
	function beforeRender()
	{
		$this->set('title', 'phpGACL :: Help');
		
		return parent::beforeRender();
	}
	
	/**
	 * Show a help page.
	 *
	 * @param string $topic	Topic to show
	 *
	 * @access public
	 * @since 1.0
	 */
	function index($topic)
	{
		$topics = array(
			'secure' => array( 'view' => 'secure', 'title' => 'How to use phpGACL to protect your controllers' ),
			'secure_plugin' => array( 'view' => 'secure_plugin', 'title' => 'Secure the phpGACL plugin from unauthorized access' )
		);
		
		if (!array_key_exists($topic, $topics))
		{
			$this->redirect($this->pluginUrl);
			exit;
		}
		
		$this->set('phpgacl_title', $topics[$topic]['title']);
		
		$this->render($topics[$topic]['view']);
	}
}

?>