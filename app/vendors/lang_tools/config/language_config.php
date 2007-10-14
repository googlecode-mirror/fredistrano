<?php

/**
 * Fichier de config du plugin
 *
 * @filesource
 * @link			http://blog.fbollon.net
 * @package			fbc.lang
 * @author 			euphrate_ylb
 */

class LANGUAGE_CONFIG {
	static $name = 'langtools';
	
	static $version = '0.1.0';

	static $prefix = 'LANG_';
	
	static $defaultLang = 'fr-FR';
	
	static $undefinedTranslation = ' --UNDEFINED-- ';

	static $langAllFileName = 'lang-all.php';

	static $supportedFileTypes = array (
		'thtml',
		'php'
	);
	
	static $supportedMode = array (
		'plugin',
		'cakephp'
	);
	
	static $supportedFormat = array (
		'CONSTANT',
		'GETTEXT'
	);
	
	static $supportedCharset = array (
		'UTF-8'
	);

	static $sourcePattern = array (
		'regexp' => "/'\#.*\#'/",
		'separation' => "##",
		'start_offset' => 2,
		'end_offset' => 2
	);

	static $templatePattern = array (
		'regexp' => "/%.*%/",
		'separation' => "##",
		'start_offset' => 1,
		'end_offset' => 1
	);

	static function getApplicationRootPath() {
		return dirname(dirname(dirname(dirname(__FILE__))));
	} // getApplicationRootPath

	static function getPluginRootPath() {
		return dirname(dirname(__FILE__));
	} // getPluginRootPath

} // LANG_CONFIG
?>