<?php 

class Utils {
	
	public static function computeDirMode( $fileMode ) {
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
	}// computeDirMode
	
	public static function getSvnRevisionFromOutput($output) {
		preg_match('/ ([0-9]+)\.$/', $output, $matches);
		if (isset($matches[1])) {
			return $matches[1];			
		} else {
			return false;
		}
	}// getRevisionFromOutput
	
	/**
	 * Convert if necessary a path to a cygwin/linux format 
	 * @param string $path 		Path to be converted
	 * @return string 			Converted path
	 */ 
	static function formatPath($path) {
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
	}// formatPath
	
}// FileSystemCommand

class ShellAction {

	public static function changePermissions() {
		
	}// changePermissions
	
	public static function createDirectory( $path=null, $mode=null ) {
		$actionLog = new ActionLog('createDirectory', $path, 'ShellAction');
		
		// Check
		if ( is_null($path) || is_null($mode) ) {
			$actionLog->error(sprintf(__('Forbidden NULL value for input parameter [%s;%s]',true),$path,$mode));	
		} else if (is_dir($path)) {
			$actionLog->error(sprintf(__('Directory %s already exists',true),$path));
		}
		
		// Execute
		$path = Utils::formatPath($path);
		if (!@mkdir($path, octdec( $mode ), TRUE)) {
			$actionLog->error( sprintf(__('Unable to create directory %s', true), $path) );
		}
		
		// Terminate action
		$actionLog->end();
		
		return $actionLog;
	}// createDirectory

	public static function executeCommand( $command = null, $options = array(), $actionLog = null ){
		// Log
		if ( is_null($actionLog) || get_class($actionLog) != 'ActionLog' ) {
			$actionLog = new ActionLog('executeCommand', $options['comment'], 'shell' );
			$terminate = false;
		} else {
			$terminate = true;
		}
		
		// Check
		if ( is_null($command) ) {
			$actionLog->error(sprintf(__('Forbidden NULL value for input parameter [%s]',true),$command));	
		} 

		// Prepare command
	    $defaultOptions = array(
			'comment' 	=> null,
			'directory'	=> null
		);
		$options = array_merge($defaultOptions, $options);
		if ( F_OS == 'WIN' ) {
	        if (is_null($options['directory'])) {
	           	$cd = 'cd '.Utils::formatPath( $options['directory'] ).'; ';
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
		
		// Execute command 
		$output = shell_exec( $prefix.$command.$suffix );
		$actionLog->saveCommand( $prefix.$command.$suffix, $output); 
		
		// End action
		if ($terminate) {
			$actionLog->end( $output );
		}
		
		return $actionLog;
	}// executeCommand
	
	public static function remove( $path, $recursive = false) {
		
	}// remove
	
	public static function runScript( $path ) {
		
	}// remove
	
	public static function synchronizeContent( $path, $recursive = false) {
	
	}// remove
	
}//

class SvnAction {
	
	public static function checkout( $svnUrl = null, $path = null, $targetDir = null, $options = array() ) {
		$actionLog = new ActionLog('checkout', $path.DS.$targetDir, 'SvnAction');
		
		// Check
		if ( is_null($svnUrl) || is_null($path) || is_null($targetDir)) {
			$actionLog->error(sprintf(__('Forbidden NULL value for input parameter [%s;%s;%s]',true),$svnUrl,$path,$targetDir));	
		} else if (!is_dir($path)) {
			$actionLog->error(sprintf(__('Directory %s not found',true),$path));
		} else if (is_dir($path.DS.$targetDir))  {
			$actionLog->error(sprintf(__('Delete target %s directory first',true), $path.DS.$targetDir));			
		}
			
		// Define step options
		$default_options = array(
			'revision' 		=> 	null,
			'user_svn' 		=> 	null,
			'password_svn' 	=> 	null
		);
		$options = array_merge($default_options, $options);
		
		// Execute command 
		$path = Utils::formatPath($path);
		$revision = !is_null($options['revision'])?' -r' . $options['revision']:'';
		$authentication = '';
		if (!is_null($options['user_svn'])) {
			$authentication .= '--username '.$options['user_svn'];
			if (!is_null($options['password_svn'])) {
				$authentication .= ' --password '.$options['password_svn'];
			}
		}

		$command = "svn --non-interactive checkout $revision $authentication $svnUrl $targetDir 2>&1";		
		$actionLog = ShellAction::executeCommand( $command,
			array(
		        'directory'	=> $path
			),
			$actionLog
		);
		
		// End action
		$actionLog->end();
		
		return $actionLog;
	}// checkout
	
	public static function export() {
		
	}// export
	
	public static function update( $projectDir, $options = array() ) {
		$actionLog = new ActionLog('update', $projectDir, 'SvnAction');
		
		if ( is_null($svnUrl) || is_null($path) || is_null($targetDir)) {
			$actionLog->error(sprintf(__('Forbidden NULL value for input parameter [%s]',true),$projectDir));	
		} else if (!is_dir($projectDir) ) {
			$actionLog->error(sprintf(__('Directory %s not found',true), $projectDir));
		} else if ( !is_dir($projectDir.DS.'.svn') ) {
			$actionLog->error(sprintf(__('Specified directory %s is not a working copy',true), $projectDir.DS.'.svn'));			
		} else if ( !is_writeable($projectDir) ) {
			$actionLog->error(sprintf(__('Specified directory %s is not writeable',true), $projectDir));			
		}
 			
		// Define step options
		$default_options = array(
			'revision' 		=> 	null
		);
		$options = array_merge($default_options, $options);
		
		// Execute command 
		$projectDir = Utils::formatPath($projectDir);
		$revision = ($options['revision']!=null)?' -r' . $options['revision']:'';
		$command = "svn update $revision $projectDir 2>&1";		
		$actionLog = ShellAction::executeCommand( $command, null, $actionLog );
		
		// End action
		$actionLog->end();
		
		return $actionLog;
	}// update
	
}// SvnAction

?>