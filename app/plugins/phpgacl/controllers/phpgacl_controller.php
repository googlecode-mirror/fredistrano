<?php

/**
 * Phpgacl Controller class file.
 *
 * Controller for Phpgacl Options actions.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Sypad, Inc.
 * @link			http://www.sypad.com SYPAD
 * @package			sypad
 * @subpackage		sypad.plugins.phpgacl.controllers
 * @since			Sypad v 1.0
 */

/**
 * The main controller for the plugin.
 *
 * @author  Mariano Iglesias
 * @package sypad
 * @subpackage sypad.plugins.phpgacl.controllers
 * @since   1.0
 */
class PhpgaclController extends PhpgaclAppController
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
	var $name = 'Phpgacl';
	
	/**
	 * Components used by this controller.
	 *
	 * @var array
	 * @since 1.0
	 */
	var $components = array( 'PhpGacl', 'Session' );
	
	/**
	 * Models used by this controller.
	 *
	 * @var array
	 * @since 1.0
	 */
	var $uses = null;
	/**#@-*/
	
	/**
	 * Shows the control panel.
	 *
	 * @access public
	 * @since 1.0
	 */
	function index()
	{
		$this->_checkInstalled();
		
		$this->set('is_index', true);
		$this->set('title', 'phpGACL :: Control Panel');
		$this->set('phpgacl_title', 'Welcome to phpGACL for CakePHP');
	}

	/**
	 * Install basic information.
	 *
	 * @access public
	 * @since 1.0
	 */	
	function install()
	{
		if ($this->PhpGacl->isInstalled())
		{
			$this->redirect($this->pluginUrl);
			exit;
		}
		
		if (!empty($this->data))
		{
			$this->_avoidTimeout();
			
			$result = $this->PhpGacl->install();
			
			if ($result)
			{
				$result = $this->PhpGacl->importControllers(dirname(__FILE__));
			}
			
			if ($result)
			{
				$this->Session->setFlash('phpGACL installed successfully');
				$this->redirect($this->pluginUrl);
				exit;
			}
			else
			{
				$this->Session->setFlash('phpGACL could not be installed');
			}
		}
		
		if (!$this->_isApiInstalled())
		{
			$this->set('vendors_path', APP . 'vendors' . DS . 'phpgacl');
			$this->set('phpgacl_web', 'http://phpgacl.sourceforge.net');
			$this->set('phpgacl_title', 'Install phpGACL API');
		}
		else
		{
			$this->set('phpgacl_title', 'Check your settings');
		}
		
		$this->set('is_index', true);
		$this->set('db_file', APP . 'config' . DS . 'database.php');
		$this->set('db_settings_default', $this->PhpGacl->getDatabaseSettings('default'));
		$this->set('db_settings', $this->PhpGacl->getDatabaseSettings());
		$this->set('title', 'phpGACL :: Installation');
	}
}

?>