<?php

/**
 * Base controller for Phpgacl controllers.
 *
 * All Phpgacl plugin controllers inherit this class.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Sypad, Inc.
 * @link			http://www.sypad.com SYPAD
 * @package			sypad
 * @subpackage		sypad.plugins.phpgacl
 * @since			Sypad v 1.0
 */

define ('PHPGACL_PLUGIN_VERSION', '1.0.2b');

/**
 * The base controller for all controllers in the plugin.
 *
 * @author  Mariano Iglesias
 * @package sypad
 * @subpackage sypad.plugins.phpgacl
 * @since   1.0
 */
class PhpgaclAppController extends AppController
{
	/**
	 * Uncomment the following lines to enable protection
	 */

	var $gacl = array(
		'get' => array (
			'type' => 'session',
			'value' => 'User'
		),
		'denied' => array (
			'type' => 'redirect',
			'value' => '/pages/denied'
		)
	);


	/**#@+
	 * @access protected
	 */
	/**
	 * Components used by all controllers.
	 *
	 * @var array
	 * @since 1.0
	 */
	var $components = array( 'PhpGacl' );

	/**
	 * Helpers used by all controllers.
	 *
	 * @var array
	 * @since 1.0
	 */
	var $helpers = array( 'PhpgaclHtml' );

	/**
	 * Base CakePHP URL where the plugin is accessible.
	 *
	 * @var string
	 * @since 1.0
	 */
	var $pluginUrl = '/phpgacl';
	/**#@-*/

	/**
	 * Runs before the action is executed. Used to make sure phpGACL is installed.
	 *
	 * @access protected
	 * @since 1.0
	 */
	function beforeFilter()
	{
		// If not installed and protection enabled, remove protection

		if (!$this->PhpGacl->isInstalled() && isset($this->gacl))
		{
			unset($this->gacl);
		}

		return parent::beforeFilter();
	}

	/**
	 * Runs after the action has been executed, and before the view is rendered.
	 *
	 * @access protected
	 * @since 1.0
	 */
	function beforeRender()
	{
		$this->set('plugin_url', $this->pluginUrl);
		$this->set('is_secured', isset($this->gacl));
		$this->set('gacl_installed', $this->PhpGacl->isInstalled());
		$this->set('gacl_api_installed', $this->_isApiInstalled());
		$this->set('version', PHPGACL_PLUGIN_VERSION);
		$this->set('cake_version', Configure::version());

		return parent::beforeRender();
	}

	/**
	 * Issues a set_time_limit to avoid timeout on heavy tasks.
	 *
	 * @access private
	 * @since 1.0
	 */
	function _avoidTimeout()
	{
		@set_time_limit(0);
	}

	/**
	 * Check if phpGACL was properly installed, if not redirect to installation.
	 *
	 * @access private
	 * @since 1.0
	 */
	function _checkInstalled()
	{
		if (!$this->PhpGacl->isInstalled())
		{
			$this->redirect($this->pluginUrl . '/install');
			exit;
		}
	}

	/**
	 * Check if phpGACL API is installed.
	 *
	 * @return bool	true if it's installed, false otherwise
	 *
	 * @access private
	 * @since 1.0
	 */
	function _isApiInstalled()
	{
		$result = true;

		$gaclClassFile = APP . 'vendors' . DS . 'phpgacl' . DS . 'gacl_api.class.php';

		if (@!file_exists($gaclClassFile))
		{
			$gaclClassFile = VENDORS . 'phpgacl' . DS . 'gacl_api.class.php';

			if (@!file_exists($gaclClassFile))
			{
				$result = false;
			}
		}

		return $result;
	}
}

?>