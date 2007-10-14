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
require_once ('..' . DS. 'libs' . DS . 'generic_parser.php');

try {
	
	echo "Test>GenericParser\n";
	$pattern = "/'\#.*\#'/";
	$input='test_file.txt';
	$output='test_file_2.txt';
	
	$parser = new GenericParser($pattern);
	$parser->loadFile($input);
	$parser->parseFile();
	$parser->saveFile($output);
	
} catch (Exception $e) {
	echo 'Caught exception: ', $e->getMessage(), "\n";
}
?>