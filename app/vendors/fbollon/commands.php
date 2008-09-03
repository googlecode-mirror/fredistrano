<?php 

class Command {
	
	/**
	 * Convert if necessary a path to a cygwin/linux format 
	 * @param string $path 		Path to be converted
	 * @return string 			Converted path
	 */ 
	static function convertPath($path) {
		$pathForRsync = $path;
		if ( F_OS == 'WIN') {
			$pattern = '/^([A-Za-z]):/';
			preg_match($pattern, $path, $matches, PREG_OFFSET_CAPTURE);
			if (!empty ($matches[1][0])) {
				$windowsLetter = strtolower($matches[1][0]);
				$pathForRsync = strtr( Configure::read('Cygwin.rootDir') . $windowsLetter . substr($path, 2), "\\", "/");
			}	
		}
		return $pathForRsync;
	}// pathConverter
	
	function dirMode($fileMode = null) {
		$fileMode = str_split($fileMode);
		$dirMode = '';
	
		for ($i=0; $i < 3; $i++) { 
			if ($fileMode[$i] > 7) {
				$fileMode[$i] = 7;
			} elseif (!in_array($fileMode[$i], array(0,7))) {
				$fileMode[$i]++;
			} 
			$dirMode .= $fileMode[$i];
		}
		return $dirMode;
	}// dirMode

	static function execute( $command, $stepLog, $options = array()){
		$defaultOptions = array(
			'comment' 	=> null,
			'directory'	=> null
		);
		$options = array_merge($defaultOptions, $options);
		
		// Logging
		$actionLog = $stepLog->addNewAction('executeCommand', $options['comment'], 'shell' );			

		// Prepare command
	    if ( F_OS == 'WIN' ) {
	        if (is_null($options['directory'])) {
	           	$cd = "cd ".self::convertPath( $options['directory'] )."; ";
	        } else {
				$cd = '';
			}
	        $prefix = "bash.exe --login -c '".$cd;
	        $suffix = "'";
	    } else {
	        if (is_null($options['directory'])) {
	           chdir($options['directory']);
	        }
	        $prefix = "";
	        $suffix = "";
	    }
		$actionLog->setCommand( $prefix.$command.$suffix ); 
    
		// Execute command 
		$output = shell_exec( $prefix.$command.$suffix );
		$actionLog->end( $output );
	
	}// executeCommand

	static function getSvnRevision($string) {
		preg_match('/ ([0-9]+)\.$/', $string, $matches);
		if (isset($matches[1])) {
			return $matches[1];			
		} else {
			return false;
		}
	}// getRevision
	
}//

?>