<?php

/**
 * Ce script permet de tester la classe LanguageManager
 * 
 * @filesource
 * @link			http://blog.fbollon.net
 * @package			fbc.lang
 * @author 			euphrate_ylb
 */

define('DS', DIRECTORY_SEPARATOR);

// require
chdir(dirname(__FILE__));
require_once ('..' . DS. 'libs' . DS . 'language_manager.php');


try {
	echo "Test>LanguageManager\n";
	$obj = new LanguageManager(array('format'=>'GETTEXT'));
	
	// Test isset
	echo "\nkey1 ".$obj->isKey('tata');
	echo "\nkey2 ".$obj->isKey('dsqqsdaffaf');
	
	echo "\nlang1 ".$obj->isSupportedLang('en-EN');
	echo "\nlang2 ".$obj->isSupportedLang('en-dqsdsqd');
	
	echo "\nboth1 ".$obj->isTranslationAvalaible('tata','en-EN');
	echo "\nboth2 ".$obj->isTranslationAvalaible('tasqdta','en-EN');
	echo "\nboth3 ".$obj->isTranslationAvalaible('tata','es-ES');
	
	// Test removal
//	$obj->removeKey('titi');
//	$this->removeTranslation('aaeaze','en-EN');
//	$obj->removeLanguage('fr-FR');
	
	// Test add 
//	$obj->addLanguage('es-BR');
	$obj->addLanguage('en-EN');
	
	$obj->addKey('azerty');
	$obj->addKey('tata');
	
	// Test set 
//	$obj->setTranslation('titi','en-EN');

//	$obj->setTranslation('titi','en-ENqsdqsdqs','yooooo');
//	$obj->setTranslation('titi','en-EN','yooooo');
//	$obj->setTranslation('titi','es-BR','yooooo');
	$obj->setTranslation('azerty','en-EN','yooooo');
	$obj->setTranslation('tata','en-EN','ge');
	
	// Test save
//	$obj->removeSupportedLanguage("en-EN");
	$obj->saveChanges(true);
	
	// Test generateLangFiles
	$obj->generateLanguageFiles();
	
	//Test get/list
//	echo $obj->getTranslationForKey('toto','en-EN');
	$tmp = $obj->listKeys();
	foreach ($tmp as $values) {
		print_r($obj->getTranslationsForKey($values));
	}
//	print_r($obj->listLanguages());
	
} catch (Exception $e) {
	echo 'Caught exception: ', $e->getMessage(), "\n";
}
?>