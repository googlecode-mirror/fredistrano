<?php

/**
 * Ce script permet de tester la classe LangTools
 * 
 * @filesource
 * @link			http://blog.fbollon.net
 * @package			fbc.lang
 * @author 			euphrate_ylb
 */

define('DS', DIRECTORY_SEPARATOR);

// Import
chdir(dirname(__FILE__));
require_once ('..' . DS. 'libs' . DS . 'parsers.php');
require_once ('..' . DS. 'libs' . DS . 'language_manager.php');

try {
	
//	echo "Test>SourceParser\n";
//	$input='test_file_1.txt';
//	$output='test_file_2.txt';
//	
//	$langManager = new LanguageManager();
//	$parser = new SourceParser($langManager);
//	$parser->loadFile($input);
//	$parser->parseFile();
//	$parser->saveFile($output);
	
	echo "Test>TemplateParser\n";
	$input='test_file_3.txt';
	$output='test_file_4.txt';
	
	$parser = new TemplateParser();
	$parser->loadFile($input);
	$parser->parseFile(array('mappingTable'=> array('CONSTANTS'=>'qhqmjkhdfbmvcvqdqqd') ));
	$parser->saveFile($output);
	
} catch (Exception $e) {
	echo 'Caught exception: ', $e->getMessage(), "\n";
}
?>