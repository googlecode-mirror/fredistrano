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

// 
require_once ('..' . DS . 'config' . DS . 'language_config.php');
require_once ('..' . DS . 'libs' . DS . 'exception' . DS . 'invalid_arg_exception.php');
require_once ('..' . DS . 'libs' . DS . 'exception' . DS . 'no_translation_exception.php');
require_once ('..' . DS . 'libs' . DS . 'exception' . DS . 'translation_changes_exception.php');
require_once ('..' . DS . 'libs' . DS . 'parsers.php');

class LanguageManager {

	var $supportedLang = array (); // Supported languages (eg. en-EN, en-US, fr-FR....)

	var $msg = array (); // All messages

	var $modified = false;

	var $format = 'CONSTANT';

	var $mode = 'plugin';

	var $overwrite = false;

	var $charset = 'UTF-8';

	function __construct($options = array ()) {
		global $supportedLang;
		global $msg;

		$this->_setParam($options, 'format');
		$this->_setParam($options, 'mode');
		$this->_setParam($options, 'overwrite');
		$this->_setParam($options, 'charset');	
		$this->_loadMasterFile();
		
	} // LangTools

	/**
	 * Add a new key
	 *
	 * @param string $key	 	New key
	 */
	function addKey($key = null) {
		if ($this->isKey($key))
			throw new InvalidArgException("Key ID already defined ($key)");

		$this->msg[$key] = array ();

		// Remember changes
		$this->modified = true;
	} // addKey

	/**
	 * Add a new language
	 *
	 * @param string $lang 		New language
	 */
	function addLanguage($lang = null) {
		if ($this->isSupportedLang($lang))
			throw new InvalidArgException("Key ID already defined ($lang)");

		$this->supportedLang[] = $lang;

		// Remember changes
		$this->modified = true;
	} // addKey

	/**
	 * Generate one lang file for each supported language
	 *
	 * The relative path starts at [project]/lang
	 *
	 * @param string $destDir 	Target directory for the generated files.
	 * @param boolean $overwrite If set to true the function will overwrite
	 * 						existing files (if any)
	 */
	function generateLanguageFiles() {
		$templateDir = LANGUAGE_CONFIG :: getPluginRootPath() . DS . 'libs' . DS . 'template';

		foreach ($this->supportedLang as $lang) {
			$destDir = $this->getOutputPath() . DS . 'locale' . DS . $lang . DS . 'LC_MESSAGES';

			// Generate file content
			$langContent = '';
			foreach ($this->msg as $key => $value) {
				try {
					$trans = $this->getTranslationForKey($key, $lang);
				} catch (NoTranslationException $e) {
					$trans = LANGUAGE_CONFIG :: $undefinedTranslation;
				}
				if ($this->format == 'CONSTANT') {
					$langContent .= "define('" . $this->getConstantFromKey($key) . "',\"$trans\");\n";
					$template = $templateDir . DS . 'default_template.php';
					$destFile = $destDir . DS . 'default.php';
				} else
					if ($this->format == 'GETTEXT') {
						$langContent .= "msgid \"" . $key . "\"\n";
						$langContent .= "msgstr \"" . $trans . "\"\n\n";
						$template = $templateDir . DS . 'default_template.po';
						$destFile = $destDir . DS . 'default.po';
					}
			} // foreach

			// Generate file from template
			$parser = new TemplateParser(array (
				'charset' => $this->charset
			));
			$parser->loadFile($template);
			$parseOptions = array (
				'mappingTable' => array (
					'CONSTANTS' => $langContent
				)
			);
			$parser->parseFile($parseOptions);
			$parser->saveFile($destFile);
		} // foreach
	} // generateLanguageFiles

	function getConstantFromKey($key) {
		return LANGUAGE_CONFIG :: $prefix . strtoupper($key);
	} // getConstantFromKey

	function getConfig() {
		return array (
			'overwrite' => $this->overwrite,
			'format' => $this->format,
			'mode' => $this->mode,
			'charset' => $this->charset
		);
	} // getConfig

	function getInputPath() {
		if ($this->mode == 'plugin')
			return LANGUAGE_CONFIG :: getPluginRootPath() . DS . 'files' . DS . 'input';
		else
			if ($this->mode == 'cakephp')
				return LANGUAGE_CONFIG :: getApplicationRootPath();
	} // getInputPath
	
	function getOutputPath() {
		if ($this->overwrite)
			return $this->getInputPath();
		else
			return LANGUAGE_CONFIG :: getPluginRootPath() . DS . 'files' . DS . 'output';
	} // getOutputPath
	

	/**
	 * Return all existing translation for a specified key
	 *
	 * @param string $key 	Specified key
	 * @return array 		All the corresponding translations
	 */
	function getTranslationsForKey($key = null) {
		if (!$this->isKey($key))
			throw new InvalidArgException("Unknown key ID ($key)");
		return $this->msg[$key];
	} // getTranslationsForKey

	/**
	 * Return the requested translation for a given key
	 *
	 * @param string $key	Specified key
	 * @return string 		All the corresponding trnaslations
	 */
	function getTranslationForKey($key = null, $lang = null) {
		if (!$this->isTranslationAvalaible($key, $lang))
			throw new NoTranslationException("No translation available for key $key and language $lang");
		return $this->msg[$key][$lang];
	} // getTranslationsForKey

	/**
	* Test if a key is already used
	*
	* @param string $key		Key ID
	* @return boolean 			True if the key already exist, False otherwise
	*/
	function isKey($key = null) {
		if ($key == null)
			throw new InvalidArgException("Missing key ID");
		return isset ($this->msg[$key]);
	} // _isKey

	/**
	 * Test if the provided language is supported
	 *
	 * @param string $lang 		Language ID
	 * @return boolean 			True if the language is supported, false otherwise
	 */
	function isSupportedLang($lang = null) {
		if ($lang == null)
			throw new InvalidArgException("Missing lang ID");
		$i = array_search($lang, $this->supportedLang);
		return ($i === false) ? false : true;
	} // _isSupportedLang

	/**
	 * Test if the requested translation is available
	 *
	 * @param string $key 		Key ID
	 * @param string $key 		Language ID
	 * @return boolean 			True if the language is supported, false otherwise
	 */
	function isTranslationAvalaible($key = null, $lang = null) {
		if ($key == null || $lang == null)
			throw new InvalidArgException("Missing translation param");
		return isset ($this->msg[$key][$lang]);
	} // _isTranslationAvalaible

	/**
	 * List all defined keys
	 *
	 * @return array 		All keys
	 */
	function listKeys() {
		return array_keys($this->msg);
	} // listSupportedLanguages

	/**
	 * List all supported languages
	 *
	 * @return array 		All supported languages
	 */
	function listLanguages() {
		return array_values($this->supportedLang);
	} // listLanguages
	
	/**
	 * Remove a language and all the corresponding translations
	 *
	 * @param string $lang 	Language ID
	 */
	function removeLanguage($lang = null) {
		if (!$this->isSupportedLang($lang))
			throw new InvalidArgException("Unsupported lang ID ($lang)");

		$this->_arrayRemoval($lang, $this->supportedLang);

		foreach ($this->msg as $key => $value) {
			try {
				$this->removeTranslation($key, $lang);
			} catch (Exception $e) {
			}
		}

		// Remember changes
		$this->modified = true;
	} // removeLanguage

	/**
	 * Remove a key (and all its translation)
	 *
	 * @param string $key 		Key ID
	 */
	function removeKey($key = null) {
		if (!$this->isKey($key))
			throw new InvalidArgException("Unsupported key ID ($key)");

		unset ($this->msg[$key]);

		// Remember changes
		$this->modified = true;
	} // removeKey

	/**
	 * Remove a translation for a key
	 *
	 * @param string $key 		Key ID
	 * @param string $lang 		Language ID of the translation
	 */
	function removeTranslation($key = null, $lang = null) {
		if (!$this->isTranslationAvalaible($key, $lang))
			throw new NoTranslationException("No translation available for key $key and language $lang");

		unset ($this->msg[$key][$lang]);

		// Remember changes
		$this->modified = true;
	} // removeTranslation

	/**
	 * Saves modification made on the current object into a file
	 *
	 * A new lang_translation.php is created
	 *
	 * @param string $destDir 	Target directory
	 * @param boolean $overwrite True if the function is allowed to overwrite an existing file
	 */
	function saveChanges($force = false) {
		if (!$this->modified && !$force)
			throw new TranslationChangesException("No change detected");

		// Content
		$langArray = $this->_arrayToCode($this->supportedLang);
		$msgArray = $this->_arrayToCode($this->msg);

		$template = LANGUAGE_CONFIG :: getPluginRootPath() . DS . 'libs' . DS . 'template' . DS . 'lang-all_template.php';
		$destFile = $this->getOutputPath() . DS . 'locale' . DS . LANGUAGE_CONFIG :: $langAllFileName;

		// Generate file from template
		$parseOptions = array (
			'mappingTable' => array (
				'LANG' => $langArray,
				'MSG' => $msgArray
			)
		);
		$parser = new TemplateParser(array (
			'charset' => $this->charset
		));
		$parser->loadFile($template);
		$parser->parseFile($parseOptions);
		$parser->saveFile($destFile);

		// Forget changes
		$this->modified = false;
	} // saveChanges

	function scanFolderForTokens($customSourceDir = null, $options = array ()) {
		if ($customSourceDir == null)
			$customSourceDir = $this->getInputPath();

		$parser = new SourceParser($this);
		$report = array (
			'processed' => array (
				'count' => 0,
				'files' => array ()
			),
			'error' => array (
				'count' => 0,
				'files' => array ()
			),
			'modified' => array (
				'count' => 0,
				'files' => array ()
			)
		);
		$this->_recursiveParse($parser, $customSourceDir, $report, $options);
		return $report;
	} // scanFolderForTokens

	/**
	 * Set a translation (new or modify)
	 *
	 * @param string $key 		Key ID
	 * @param string $lang 		Language ID of the translation
	 * @param string $translation Translation
	 */
	function setTranslation($key = null, $lang = null, $translation = null) {
		if (!$this->isKey($key))
			throw new InvalidArgException("Unknown key ID ($key)");
		if (!$this->isSupportedLang($lang))
			throw new InvalidArgException("Unsupported lang ID ($lang)");
		if ($translation == null)
			throw new InvalidArgException("Missing translation");

		$this->msg[$key][$lang] = $translation;
		$this->modified = true;
	} // setTranslation

	function setCharset($charset = null) {
		if (!in_array($charset, LANGUAGE_CONFIG :: $supportedCharset))
			throw new InvalidArgException("Unsupported mode ($charset)");

		$this->charset = $charset;
	} // setCharset

	function setFormat($format = null) {
		if (!in_array($format, LANGUAGE_CONFIG :: $supportedFormat))
			throw new InvalidArgException("Unsupported output format ($format)");

		$this->format = $format;
	} // setFormat

	function setMode($mode = null) {
		if (!in_array($mode, LANGUAGE_CONFIG :: $supportedMode))
			throw new InvalidArgException("Unsupported mode ($mode)");
		
		$this->mode = $mode;
		$this->_loadMasterFile();
	} // setMode

	function setOverwrite($overwrite = false) {
		$this->overwrite = $overwrite;
	} // setOverwrite

	/**
	 * Recursive function for converting an array to a source code
	 *
	 * @param $array 		Array to be converted
	 * @param $indent 		Indent level
	 * @return string 		Corresponding source code
	 */
	private function _arrayToCode($array = null, $indent = "") {
		$content = " array (\n";
		$count = 0;
		$size = sizeof($array);
		$openIndent = $indent . "\t";
		$closeIndent = $indent;
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$str = $this->_arrayToCode($value, $openIndent);
			} else
				$str = "\"" . $value . "\"";

			$content .= "$openIndent'$key' => " . $str . "";
			if (++ $count == ($size))
				$content .= "\n";
			else
				$content .= ",\n";
		}
		$content .= "$closeIndent)";

		return $content;
	} // _arrayToCode

	/**
	 * Remove a value from an array
	 *
	 * @param $val 			Value that should be removed
	 * @param $array 		Corresponding array
	 */
	private function _arrayRemoval($val, & $array) {
		$i = array_search($val, $array);
		unset ($array[$i]);
	} // _arrayRemoval

	private function _loadMasterFile() {
		$customLangAllFile = $this->getInputPath() . DS . 'locale' . DS . LANGUAGE_CONFIG :: $langAllFileName;
	
		if (!file_exists($customLangAllFile)) {
			$supportedLang = array ();
			$msg = array ();
		} else
			require ($customLangAllFile);

		$this->supportedLang = $supportedLang;
		$this->msg = $msg;
		ksort($this->msg);
	} // _loadMasterFile
	
	private function _setParam($options, $option) {
		if (isset ($options[$option])) {
			$func = 'set' . ucfirst($option);
			$this-> {
				$func }
			($options[$option]);
		}
	} // _setParam

	private function _recursiveParse($parser, $recDir, & $report, $options = array ()) {
		$simulate = isset ($options['simulate']) ? $options['simulate'] : false;
		$verbose = isset ($options['verbose']) ? $options['verbose'] : false;
		$backup = isset ($options['backup']) ? $options['backup'] : false;

		$dir = openDir($recDir);
		$ext = LANGUAGE_CONFIG :: $supportedFileTypes;

		while ($file = readDir($dir)) {
			if (($file != ".") && ($file != "..")) {
				if (is_dir($recDir . DS . "$file") && ($file != LANGUAGE_CONFIG :: $name)) {
					// Recursive call on directory 
					$this->_recursiveParse($parser, $recDir . DS . "$file", $report, $options);
				} else {
					// Processing a file
					$cheminComplet = $recDir . DS . $file;
					$filename = basename($cheminComplet);
					$extension = $this->_filenameExtension($filename);

					if (in_array($extension, $ext)) {
						$report['processed']['count'] += 1;
						// Step 1 - choisir un fichier [boucle a faire sur les fichiers d un dir]
						$sourceFile = $cheminComplet;
						$destFile = $this->overwrite ? $sourceFile : $this->getOutputPath() . DS . $filename;

						if ($verbose) {
							echo $cheminComplet;
							//$report['processed']['files'][] = $sourceFile;
						}
						
						// Step 2 - parse du fichier
						$parser->loadFile($sourceFile);
						try {
							if ($parser->parseFile()) {
								// Sauvegarde 
								if (!$simulate)
									$parser->saveFile($destFile, $backup);
									
								$report['modified']['count'] += 1;
								
								if ($verbose) {
									echo " [m] > $destFile";
									//$report['modified']['files'][] = $destFile;
								}
							} // if
						} catch (Exception $e) {
							$report['error']['count'] += 1;
							if ($verbose) {
								echo ' [e]';
								//$report['error']['files'][] = $destFile;
							}
						} // try/catch
						if ($verbose) {
							echo "\n";
							//$report['error']['files'][] = $destFile;
						}
					} //if 
				} // if/else
			} // if 
		} // while
		closeDir($dir);
	} // _recursiveParse

	/**
	* Return the extension of the file
	*/
	private function _filenameExtension($filename) {
		$pos = strrpos($filename, '.');
		return ($pos === false) ? false : substr($filename, $pos +1);
	} // _filenameExtension

} // LangTools
?>