<?php
/**
 * Ce script définit la structure generique d'une commande
 *
 * @filesource
 * @link			http://blog.fbollon.net
 * @package			fbc.lang
 * @author 			euphrate_ylb
 */
 
 if (!defined('DS'))
  	define('DS', DIRECTORY_SEPARATOR);
  	
 require_once ('..' . DS . 'config' . DS . 'language_config.php'); 
 require_once ('..' . DS . 'libs' . DS . 'language_manager.php');
 require_once ('..' . DS . 'libs' . DS . 'parsers.php');

 class GenericCommand {
 	var $options 			= array( );
 	
 	var $actions 			= array();
 	
 	var $basicActions 		= array('help'=>'doHelp', 'quit' => 'doQuit', 'about' => 'doAbout');
 	
 	var $specificActions	= array();
 	
 	function __construct($argv) {	
	    $this->actions = array_merge($this->basicActions,$this->specificActions);
	    
	    if (  sizeof($argv) != 0 ) {
			if (isset($this->basicActions[$argv[0]])) {
				$this->options['action'] = $argv[0];
			}else {
				// Command line setup	
				try {
					$this->getOptionsFromCommandLine($argv);	
				} catch (Exception $e) {
					echo "Error [".get_class($e)."] : ".$e->getMessage()."\n"; 
					$this->options['action'] = 'help';
				} 
			}
	    } else 
	    	// User interactive setup
	    	$this->getOptionsFromUserInputs();
 	}// __construct
 	
 	// Abstract 
 	function getOptionsFromCommandLine() {}
 	function getOptionsFromUserInputs() {}
 		
 	function run() {
 		// Start requested action
 		$requestedAction = $this->options['action'];
 		$method = $this->actions[$requestedAction];
		try {
			$this->{$method}(); 	
		} catch (Exception $e) {
			echo "Error [".get_class($e)."] : ".$e->getMessage()."\n"; 
			$this->options['action'] = 'help';
		} 
 	}// run
 	
 	function doAbout() {
 		 echo "Written by euphrate_ylb [email_ylb-langtools@yahoo.fr] & fbollon [fbollon@gmail.com].\n" .
 			"Feel free to send them suggestions, bug reports...\n";	
 	}// doAbout
 	
 	function doHelp() {
 		echo "No help available\n";	
 	}// doHelp
 	
 	function doQuit() {
 		echo "Bye...";
 		exit();
 	}// doQuit
 	
 	function isUnsupported($operation) {
 		echo "Requested operation is not supported : $operation \n";	
 	}// doHelp
 	
 	
 	protected function drawLine() {
 		echo "------------------------------------------------------\n";
 	}// drawLine
 	
 	protected function getCorrectUserInputFromActions( $options = array()) {
 		return $this->getCorrectUserInput(array_keys($this->actions), $options); 
 	}// getCorrectUserInputFromActions
 	
 	protected function getCorrectUserInput($allowedResponses, $options = array()) {
 		$beforeText 	= isset($options['beforeText'])?$options['beforeText']:'';
 		$afterText 		= isset($options['afterText'])?$options['afterText']:'';
 		$errorText 		= isset($options['errorText'])?$options['errorText']:'inputErrors';
 		$level	 		= isset($options['level'])?$options['level']:'';
 		
 		echo $beforeText;
 		echo "\n". $level."> ";
 		$response = trim(fgets(STDIN));
 		$response = explode(' ',$response);
 		while (!in_array($response[0], $allowedResponses)) {
 			echo "$level"."[".$errorText."]> ";
 			$response = trim(fgets(STDIN));
 			$response = explode(' ',$response);
 		}// while 
 		echo $afterText;
 		return $response;
 	}// getCorrectUserInput
 }// GenericCommand 

 class MainCommand extends GenericCommand {
	var $specificActions = array('clean' => 'doClean','list' => 'doList', 'scan' => 'doScan');
		
  	function __construct($argv) {
 		parent::__construct($argv);
 	}// __construct
 	
 	// Abstract 
 	function getOptionsFromCommandLine($argv) {
 		$this->options['action'] = array_shift($argv);
 		$this->options['options'] = $argv;
 	}// getOptionsFromCommandLine
 	
 	function getOptionsFromUserInputs() {
 		$response = $this->getCorrectUserInputFromActions();
		$this->options['action'] = array_shift($response);
		$this->options['options'] = $response;
	}// getOptionsFromUserInputs
 	
 	function doList() {
 		$this->_execCommand('ListCommand');	
 	}// doList
 	
 	function doScan() {
 		$this->_execCommand('ScanSourcesCommand');	
 	}// doScan
 	
 	function doHelp() {
 		$this->drawLine();
 		//echo "Usage : php.exe langTools.php list [keys|langs]\n";
 		echo "Description :\n" .
 			" Main menu. You may provide directly required inline arguments '[command] help' or '[command] [args]'\n";
 		echo "Available commands :\n" .
 			"  list\t\tList language data\n" .
 			"  scan\t\tScan sources for language tokens\n".
			"  help\t\tNeed help\n" .
			"  quit\t\tExit...\n";	
 	}// doHelp
 	
 	private function _execCommand($command){
		$this->drawLine();
		echo "Start $command \n";
		$this->drawLine();
		$command = new $command ($this->options['options'] );
		$command->run();		
		$this->drawLine();
 	}
 }// MainCommand


 class ListCommand extends GenericCommand {	
 	var $langManager = null;
	var $specificActions = array ('keys' => 'doKeys', 'langs'=> 'doLangs');
	
 	function __construct($argv) {
 		$this->langManager = new LanguageManager();				
 	
 		parent::__construct($argv);	
 	}// __construct
 	
 	function getOptionsFromCommandLine($argv) {
 		$size = sizeof($argv);
	
		// Which command
 		if (in_array($argv[$size-1],array_keys($this->specificActions))) {
 			$this->options['action'] = $argv[$size-1];
 			
 			// Options (if any?)
	 		if ( $size > 1 ) {
	 			// Parse options
	 			if ( $argv[0][0] == '-') {
		 			$flags = $argv[0];
		 			$count = 1;
		 			while ($flags = substr($flags,1)){
		 				switch ($flags[0]) {
							case 'M':
								if (isset($argv[$count])) {
									$this->langManager->setMode($argv[$count]);
									$count++;
								} else {
									echo "Error : Mode expected as argument with '-M' flag\n";
									$this->options['action'] = 'help'; 
								}
								break;
							default:
								echo "Error : Unsupported flag (".$flags[0].")\n";
								$this->options['action'] = 'help';
								return;
						}// switch
		 			} // while 
	 			}	else {
		 			echo "Error : Bad flag syntax (".$argv[0].")\n";
					$this->options['action'] = 'help';				
		 		}
	 		}
 		} else {
			echo "Error : Unsupported command (".$argv[$size-1].")\n";
	 		$this->options['action'] = 'help'; 			
 		}
	}// getOptionsFromCommandLine
 	
 	function getOptionsFromUserInputs() {
		$this->options['action'] = 'help';
 	}// getOptionsFromUserInputs
 	
 	function doKeys() {
 		$keys  = $this->langManager->listKeys();
		$count = 0 ;
		foreach ($keys as $key) {
			$count++;	
			echo "  ".$key."\n";
		}// foreach
		echo "Execution report\n  Listed keys : \t".$count."\n";
 	}//

 	function doLangs() {
		$languages  = $this->langManager->listLanguages();
		$count = 0 ;
		foreach ($languages as $language) {
			$count++;	
			echo $language."\n";
		}// foreach
		$this->drawLine();
		echo "Execution report\n  Listed languages : \t".$count."\n";
 	}// 	
 
 	function doHelp() {
		$this->drawLine();
 		
		echo "Usage : php.exe langTools.php list [-M value] [keys|langs]\n";
 		echo "\nDescription :\n" .
 			" List the current content of the 'lang' file\n";
		echo "\nAvailable commands:\n" .
 			"  keys\t\tlist all existing keys\n" .
 			"  langs\t\tlist all existing languages\n";
		echo "\nFlags details:\n" .
			"  -M\t\tmode [value]- set processing to a specified value (cakephp,plugin)\n";
		echo "\nEx :\n" .
			" php langTools.php list -M cakephp keys\n".
			" php langTools.php list langs\n";
	 }// doHelp
 	
 }// ListCommand

 class ScanSourcesCommand extends GenericCommand {	
	var $langManager 	= null;
	
	var $parser 		= null;
	
	var $specificActions = array ('files' => 'doFiles');

 	function __construct($argv) {
 		$options = array ('mode'=>'plugin','charset'=>'UTF-8','format'=>'CONSTANT');	
 		$this->langManager = new LanguageManager($options);
 		
 		// Options for action
 		$this->options['baseDir'] = $this->langManager->getInputPath();
 		$this->options['simulate'] = false;	
 		$this->options['verbose'] = false;	
 		$this->options['backup'] = false;	
 		
 		parent::__construct($argv);
 	}// __construct
 	
 	function getOptionsFromCommandLine($argv) {
 		$size = sizeof($argv);
 		// Which command
 		if (in_array($argv[$size-1],array_keys($this->specificActions))) {
 			$this->options['action'] = $argv[$size-1];
 			
 			// Options (if any?)
	 		if ( $size > 1 ) {
	 			// Parse options
	 			if ( $argv[0][0] == '-') {
		 			$flags = $argv[0];
		 			$count = 1;
		 			while ($flags = substr($flags,1)){
		 				switch ($flags[0]) {
							case 'b':
								$this->options['backup'] = true;	
								break;
							case 'v':
								$this->options['verbose'] = true;
								break;
							case 's':
								$this->options['simulate'] = true;
								break;
							case 'o':
								$this->langManager->setOverwrite(true);
								break;
							case 'C':
								if (isset($argv[$count])) {
									$this->langManager->setCharset($argv[$count]);
									$count++;
								} else {
									echo "Error : Charset expected as argument with '-C' flag\n";
									$this->options['action'] = 'help'; 
								}
								break;
							case 'M':
								if (isset($argv[$count])) {
									$this->langManager->setMode($argv[$count]);
									$this->options['baseDir'] = null;		
									$count++;
								} else {
									echo "Error : Mode expected as argument with '-M' flag\n";
									$this->options['action'] = 'help'; 
								}
								break;
							case 'F':
								if (isset($argv[$count])){
									$this->langManager->setFormat($argv[$count]);
									$count++;
								} else {
									echo "Error : Format expected as argument with '-F' flag\n";
									$this->options['action'] = 'help'; 
								}
								break;
							case 'D':
								if (isset($argv[$count])){
									$this->options['baseDir'] = $argv[$count];
									$count++;
								} else {
									echo "Error : Directory expected as argument with '-R' flag\n";
									$this->options['action'] = 'help'; 
								}
								break;
							default:
								echo "Error : Unsupported flag (".$flags[0].")\n";
								$this->options['action'] = 'help';
								return;
						}// switch
		 			} // while 
	 			}	else {
		 			echo "Error : Bad flag syntax (".$argv[0].")\n";
					$this->options['action'] = 'help';				
		 		}
	 		}
 		} else {
			echo "Error : Unsupported command (".$argv[$size-1].")\n";
	 		$this->options['action'] = 'help'; 			
 		}
 		
 	}// getOptionsFromCommandLine
 	
 	function getOptionsFromUserInputs() {
 		$this->options['action'] = 'help'; 
// 		$inputOptions = array('level'=>'scan');
// 		$response = $this->getCorrectUserInputFromActions($inputOptions);
//		$this->options['action'] = array_shift($response);
//		
//		if ($this->options['action']=='files') {
//			// User manual setup
//			echo "Scan with langTools the following path: " . $this->options['baseDir'] . "\n[Y] >";
//			$response = trim(fgets(STDIN));
//			if (($response != 'Y') && ($response != '')) {
//				echo 'Please enter a path :';
//				$this->options['baseDir'] = trim(fgets(STDIN));
//			}
//	
//			echo "Activate simulation mode\n[N] >";
//			$response = trim(fgets(STDIN));
//			if (($response != 'N') && ($response != ''))
//				$this->options['simulate'] = true;
//				
//			echo "Process in verbose mode\n[N] >";
//			$response = trim(fgets(STDIN));
//			if (($response != 'N') && ($response != ''))
//				$this->options['verbose'] = true;
//				
//			echo "Create backup files\n[N] >";
//			$response = trim(fgets(STDIN));
//			if (($response != 'N') && ($response != ''))
//				$this->options['backup'] = true;		
//		}
 	}// getOptionsFromUserInputs
 	
 	function doFiles() { 	
 		// config
 		$config = $this->langManager->getConfig();
 		echo 	"Processing ...\n" ;
 		echo 	"- langManger : charset [".$config['charset']."] - mode [".$config['mode']."] - " .
 					"format [".$config['format']."] - overwrite[".$config['overwrite']."]\n";
 		echo 	"- Parser  : source [".$this->langManager->getInputPath()."] - ".
 					"simulate [".$this->options['simulate']."] - verbose [".$this->options['verbose']."] - " .
 					"backup [".$this->options['backup']."]\n";
 	
 		// Parse all files
 		echo "Scanning files...\n";
 		$report = $this->langManager->scanFolderForTokens($this->options['baseDir'],$this->options);

 		$this->drawLine();
 		echo "Execution report\n";
		$this->drawLine();
		echo "  Processed files : \t".$report['processed']['count'].
			"\n  Generated errors : \t".$report['error']['count'].
			"\n  Modified files : \t".$report['modified']['count']."\n";
 		
 		// Updates lang files
		$this->_regenerateLangFiles();
 	}// run 
 	
  	function doHelp() {
  		$this->drawLine();
 		echo "Usage : php.exe langTools.php scan [[-bsvo[CMRFD [values]*]] files]\n";
 		echo "\nDescription :\n" .
 			" Scan source files for discovering new language tokens\n";
		echo "\nAvailable commands:\n" .
 			"  [empty]\t\tscan files and let you set up options at runtime\n" .
 			"  files\t\tscan files according to the specified flags\n";
		echo "\nFlags details:\n" .
 			"  -b\t\tbackup - backup existing files\n" .
 			"  -s\t\tsimulate - simulate execution (don't create or modify anything)\n" .
 			"  -v\t\tverbose - display informations while processing\n".
 			"  -o\t\toverwrite - overwrite input files\n".
 			"  -C\t\tcharset [value] - set charset to a specified value (UTF-8, IS0...)\n".
			"  -M\t\tmode [value]- set processing to a specified value (cakephp,plugin)\n".
			"  -F\t\tformat [value] - set output format to a specified value (CONSTANT,GETTEXT)\n".
			"  -D\t\tdirectory [value] - set root directory for parsing to a specified value\n";
		echo "\nEx :\n" .
			" php langTools.php scan -bvFM CONSTANT app files\n".
			" php langTools.php scan files\n". 
			" php langTools.php scan\n";
 	}// help
 	
	private function _regenerateLangFiles() {
		$this->langManager->saveChanges(true);
		$this->langManager->generateLanguageFiles();
	} // _regenerateLangFiles
 	
 }// ScanSourcesCommand
?>