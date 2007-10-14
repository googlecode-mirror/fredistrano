<?php


/**
 * Ce script gere le fichier de langue et génére les imports
 *
 * @filesource
 * @link			http://blog.fbollon.net
 * @package			fbc.lang
 * @author 			euphrate_ylb
 */

if (!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

class GenericParser {
	var $charset = 'UTF-8'; 
	
	var $modified = false;
	
	var $searchPattern = null;

	var $filename = null;

	var $content = null;

	function __construct($options = array()) {
		$this->_setParam($options, 'charset');
		$this->_setParam($options, 'searchPattern');
	} // __construct

	function loadFile($filename = null) {
		if ($filename == null)
			throw new InvalidArgException("Missing filename");
	
		$handle = fopen($filename, 'r');
		if (filesize($filename)!=0)
			$this->content = fread($handle, filesize($filename));
		else
			$this->content = '';
		
		fclose($handle);

		$this->filename = $filename;
		// Follow modifications
		$this->modified = false;
	} //loadFile

	function saveFile($filename = null, $backup = false ) {
		if ($filename == null)
			// Take the same file as load()
			$filename = $this->filename;

		$dirs = explode(DS, $filename);
		// Different root folder between linux & windows
		$path = ($dirs[0] == '') ? '/' : $dirs[0] . DS;
		for ($index = 1; $index < (sizeof($dirs) - 1); $index++) {
			$path .= $dirs[$index];
			if (!file_exists($path))
				mkdir($path);
			$path .= DS;
		}

		if ($backup && file_exists($filename)) 
			//backup
			copy($filename, $filename.'~');
		
		$handle = fopen($filename, 'w');
		fwrite($handle, $this->content);
		fclose($handle);

		// Follow modifications
		$this->modified = false;
	} // saveFile

	function parseFile($options = array ()) {
		// Extract token
		preg_match_all($this->searchPattern['regexp'], $this->content, $matches);
		$matches = $matches[0];

		if (empty ($matches))
			return false;

		// Get values
		$replacementStrings = null;
		foreach ($matches as $key => $value) {
			$replacementString = null;
			$start = $this->searchPattern['start_offset'];
			$stop = $this->searchPattern['end_offset'];
			$token = substr($value, $start, strlen($value) - ($start + $stop));

			if (!empty ($options))
				if (isset ($options['mappingTable']))
					if (isset ($options['mappingTable'][$token]))
						$replacementString = $options['mappingTable'][$token];

			$replacementStrings[$key] = $this->processToken($token, $replacementString);
		} //foreach

		// Replace token
		foreach ($matches as $key => $value)
			$this->content = str_replace($value, $replacementStrings[$key], $this->content);

		return true;
	} // parseFile

	function processToken($token, $staticReplacementString = null) {
		return ($staticReplacementString == null) ? '' : $staticReplacementString;
	} // processToken
	
	
	function setCharset($charset = null) {
		if (!in_array($charset, LANGUAGE_CONFIG :: $supportedCharset))
			throw new InvalidArgException("Unsupported mode ($mode)");

		$this->charset = $charset;
	} // setCharset
	
	function setSearchPattern($searchPattern = null) {
		$this->searchPattern = $searchPattern;
	} // setSearchPattern
	
	private function _setParam($options, $option) {
		if (isset ($options[$option])) {
			$func = 'set' . ucfirst($option);
			$this-> {
				$func }
			($options[$option]);
		}
	} // _setParam
	
} // GenericParser

class SourceParser extends GenericParser {
	var $langManager = null;

	function __construct($langManager=null) {
		$options['searchPattern'] = LANGUAGE_CONFIG :: $sourcePattern;
		$options['charset'] = $langManager->charset;
		parent :: __construct($options);

		$this->langManager = $langManager;
	} // __construct

	function processToken($token, $staticReplacementString = null) {
		$sep = $this->searchPattern['separation'];
		$data = explode($sep, $token);
		$langManager = $this->langManager;

		if (!$langManager->isKey($data[0]))
			$langManager->addKey($data[0]);

		for ($i = 0; $i < (sizeof($data) - 1) / 2; $i++) {
			if (!$langManager->isSupportedLang($data[2 * $i +1]))
				$langManager->addLanguage($data[2 * $i +1]);

			$langManager->setTranslation($data[0], $data[2 * $i +1], $data[2 * $i +2]);
		} //

		return $langManager->getConstantFromKey($data[0]);
	} // processToken

} // SourceParser

class TemplateParser extends GenericParser {

	function __construct($options = array()) {
		$options['searchPattern'] = LANGUAGE_CONFIG :: $templatePattern;
		parent :: __construct($options);
	} // __construct

} // TemplateParser
?>