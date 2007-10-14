<?php


/**
 * Experimental
 *
 * @filesource
 * @link			http://blog.fbollon.net
 * @package			fbc.lang
 * @author 			euphrate_ylb
 */

define('DS', DIRECTORY_SEPARATOR);

// Import
chdir(dirname(__FILE__));
require_once ('..' . DS . 'libs' . DS . 'commands.php');

echo 'Welcome to '.LANGUAGE_CONFIG :: $name.' v'.LANGUAGE_CONFIG :: $version."\n\n";

array_shift($argv);
$continue = false;
if ( sizeof($argv) > 0)
 	$continue = false;
else {
	$continue = true;
	echo "Type 'help' for usage information";
}

do {
 	$command = new MainCommand($argv);
	$command->run();
	unset($command);
} while ($continue);

?>