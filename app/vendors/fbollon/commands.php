<?php 

class Utils {
	
	public static function computeDirMode( $fileMode, $options = array() ) {
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
	
	public static function getSvnRevisionFromOutput($output, $options = array() ) {
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

class Action {
	
	protected function initAction($name=null, $comment=null, $type=null, $options = array() ) {
		if ( isset($options['stepLog']) && get_class($options['stepLog']) == 'StepLog' ) {
			$actionLog = $options['stepLog']->addNewAction($name, $comment , $type);
		
		} else if ( isset($options['actionLog']) && get_class($options['actionLog']) == 'ActionLog' ) {
			$actionLog = $options['actionLog'];
		
		} else {
			$actionLog = new ActionLog($name, $comment , $type);	
		}

		return $actionLog;
	}// initAction
	
}// Action

class ShellAction extends Action {

	public static function changePermissions( $path=null, $mode=array(), $options = array() ) {
		if (!is_array($mode)) {
			$mode = array(
				'file' 	=> $mode,
				'dir' 	=> Utils::computeDirMode($mode)
			);
		}
		
		// Check
		if ( is_null($path) || empty($mode) ) {
			$actionLog->error(sprintf(__('Forbidden NULL value for input parameter [%s;%s]',true),$path,$mode));	
		}

		if (!file_exists($path)) {
			$actionLog->error(sprintf(__('Path [%s] not found',true),$path));	
		}

		$path = Utils::formatPath($path);
		
		if (isset($mode['file'])) {
			// Change file mode
			$comment = sprintf(__('Reseting permissions files on %s',true),$path);
			$actionLog = self::initAction('resetPermission', $comment, 'ShellAction', $options);
			$command = "find ".$path." -type f -exec chmod ".$mode['file']." {} \;";
			ShellAction::executeCommand( $command,
				array(
			        'directory'	=> $path,
					'actionLog' => $actionLog
				)
			);
		}

		if (isset($mode['dir'])) {
			// Change directory mode
			$comment = sprintf(__('Resetting directories permissions on %s',true),$path);
			$actionLog = self::initAction('resetPermission', $comment, 'ShellAction', $options);
			$command = "find ".$path." -type d -exec chmod ".$mode['dir']." {} \;";
			ShellAction::executeCommand( $command,
				array(
			        'directory'	=> $path,
					'actionLog' => $actionLog
				)
			);
		}
		
		// Terminate action
		$actionLog->end();
		
		return $actionLog;
	}// changePermissions
	
	
	
	public static function createDirectory( $path=null, $mode=null, $options = array() ) {
		$comment = sprintf(__('Creating %s with mode %s',true),$path,$mode);
		$actionLog = self::initAction('createDirectory', $comment, 'ShellAction', $options);

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

	public static function executeCommand( $command = null, $options = array() ){
		$defaultOptions = array(
			'comment' 	=> null,
			'directory'	=> null
		);
		$options = array_merge($defaultOptions, $options);

		// Log management
		$actionLog = self::initAction('executeCommand', $options['comment'], 'ShellAction', $options);
		if ( !isset($options['actionLog']) || get_class($options['actionLog']) != 'ActionLog' ) {
			// Terminate log if passed in params
			$terminate = false;
		} else {
			$terminate = true;
		}
		
		// Check
		if ( is_null($command) ) {
			$actionLog->error(sprintf(__('Forbidden NULL value for input parameter [%s]',true),$command));	
		} 

		// Prepare command
		if ( F_OS == 'WIN' ) {
	        if (!is_null($options['directory'])) {
	           	$cd = 'cd '.Utils::formatPath( $options['directory'] ).'; ';
	        } else {
				$cd = '';
			}
	        $prefix = "bash.exe --login -c '".$cd;
	        $suffix = "'";
	    } else {
	        if (!is_null($options['directory'])) {
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
	
	public static function remove( $path, $recursive = false , $options = array() ) {
		$comment = sprintf(__('Deleting content under %s (recursivity=%d)',true), $path, $recursive);
		$actionLog = self::initAction('remove', $comment, 'ShellAction', $options);
		
		// Check
		if (!is_dir($path)) {
			$actionLog->error( sprintf(__('Nothing to delete since no temporary files have been found', true)) );
		} 			

		// Execute command 
		$path = Utils::formatPath($path);
		$recStr = $recursive?'r':'';
		$command = "rm -f".$recStr." ".$path;
		ShellAction::executeCommand( $command, array('actionLog' => $actionLog) );

		// End action
		$actionLog->end();
		
		return $actionLog;
	}// remove
	
	public static function runScript( $type, $projectTmpDir, $scriptPath, $options ) {
		$comment = sprintf(__('Executing script %s',true), $scriptPath);
		$actionLog = self::initAction('runScript', $comment, 'ShellAction', $options);
		
		if (!isset($scriptPath) || !$scriptPath) {
			$actionLog->error( sprintf(__('Script not found', true)) );
		}
		
		// Run script
		if ($options['run'.ucfirst($type).'Script']) {
			if (!file_exists($scriptPath) && file_exists($projectTmpDir.'tmpDir'.DS.'.fredistrano'.DS.$scriptPath)) {
				$scriptPath = $projectTmpDir.'tmpDir'.DS.'.fredistrano'.DS.$scriptPath;
			} else if (!file_exists($scriptPath)){
				$actionLog->error( __('Script not found', true) );
			}
		
			if (!is_executable($scriptPath)) {
				$log = ShellAction::executeCommand( "chmod u+x $scriptPath", 
					array(
						'comment'	=> __('Execution privileges to script',true),
						'actionLog' => $actionLog
					)
				);	
			}
			
			ShellAction::executeCommand( $scriptPath,
				array(
					'comment'	=> sprintf(__('%s script',true), $type),
					'actionLog' => $actionLog
				)
			);
		}
		// End action
		$actionLog->end();
		
		return $actionLog;
	}// runScript
	
	public static function synchronizeContent( $source = null, $target = null, $options = array ()) {
		$comment = sprintf(__('Synchronizing %s with %s',true),$source,$target);
		$actionLog = self::initAction('synchronizeContent', $comment, 'ShellAction', $options);

		// Setting up Rsync options
		if ($options['simulation'] === true) {
			// Simulation mode
			$option = 'rtvn';
		} else {
			// Live mode
			$option = 'rtv';
			
			//The rsync option "O" not yet supported on Mac
			if ( F_OS != 'DAR') {
				$option .= 'O';
			}
		}
		
		// Execute command		
		$excludeFileName = Utils::formatPath( $options['exclude'] );
		$source = Utils::formatPath( $source );
		$target = Utils::formatPath( $target );
	
		$command = "rsync -$option --delete --exclude-from=$excludeFileName $source $target 2>&1";	
		ShellAction::executeCommand( $command, array('actionLog' => $actionLog));
		
		// End action
		$actionLog->end();
		
		return $actionLog;
	}// synchronizeContent
	
}//

class SvnAction extends Action {
	
	public static function checkout( $svnUrl = null, $path = null, $targetDir = null, $options = array() ) {
		$actionLog = self::initAction('checkout',$svnUrl,'SvnAction',$options);
		$configDirectory = '';
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
			'revision' 			=> null,
			'user_svn' 			=> null,
			'password_svn' 		=> null,
			'configDirectory'	=> null,
			'parseResponse' 	=> false
		);
		$options = array_merge($default_options, $options);
		
		// Execute command 
		$path = Utils::formatPath($path);
		$revision = !is_null($options['revision'])?' -r' . $options['revision']:'';
		$authentication = '';
		if (!is_null($options['user_svn'])) {
			$authentication .= '--username '.$options['user_svn'];
			if (!empty($options['password_svn'])) {
				$authentication .= ' --password '.$options['password_svn'];
			}
		}
		if (!is_null($options['configDirectory'])) {
			$configDirectory = '--config-dir '.Utils::formatPath( $options['configDirectory'] );
		}
		$command = "svn checkout --non-interactive $configDirectory $revision $authentication $svnUrl $targetDir 2>&1";		
		ShellAction::executeCommand( $command,
			array(
		        'directory'	=> $path,
				'actionLog' => $actionLog
			)
		);
		// Parse output
		if ( !(strpos( $actionLog->getResult() , 'PROPFIND request failed on' ) === false) && $options['parseResponse'] ) {
			$actionLog->error(__('An error has been detected in the SVN output',true));			
		}
		
		// End action
		$actionLog->end();
		
		return $actionLog;
	}// checkout
	
	public static function export( $svnUrl = null, $path = null, $targetDir = null, $options = array() ) {
		$actionLog = self::initAction('export',$svnUrl,'SvnAction',$options);
		$configDirectory = '';
		
		if ( is_null($svnUrl) || is_null($path) || is_null($targetDir)) {
			$actionLog->error(sprintf(__('Forbidden NULL value for input parameter [%s;%s;%s]',true),$svnUrl,$path,$targetDir));	
		} else if (!is_dir($path)) {
			$actionLog->error(sprintf(__('Directory %s not found',true),$path));
		} else if (is_dir($path.DS.$targetDir))  {
			$actionLog->error(sprintf(__('Delete target %s directory first',true), $path.DS.$targetDir));			
		}
		
		// Define step options
		$default_options = array(
			'revision' 			=> null,
			'user_svn' 			=> null,
			'password_svn' 		=> null,
			'configDirectory'	=> null,
			'parseResponse' 	=> false
		);
		$options = array_merge($default_options, $options);
		
		// Execute command 
		$path = Utils::formatPath($path);
		$revision = !is_null($options['revision'])?' -r' . $options['revision']:'';
		$authentication = '';
		if (!is_null($options['user_svn'])) {
			$authentication .= '--username '.$options['user_svn'];
			if (!empty($options['password_svn'])) {
				$authentication .= ' --password '.$options['password_svn'];
			}
		}
		if (!is_null($options['configDirectory'])) {
			$configDirectory = '--config-dir '.Utils::formatPath( $options['configDirectory'] );
		}
		$command = "svn export --non-interactive $configDirectory $revision $authentication $svnUrl $targetDir 2>&1";	
		ShellAction::executeCommand( $command,
			array(
		        'directory'	=> $path,
				'actionLog' => $actionLog
			)
		);
		// Parse output
		if ( !(strpos( $actionLog->getResult() , 'PROPFIND request failed on' ) === false) && $options['parseResponse'] ) {
			$actionLog->error(__('An error has been detected in the SVN output',true));			
		}
		
		// End action
		$actionLog->end();
		
		return $actionLog;
	}// export
	
	public static function update( $projectDir, $options = array() ) {
		$actionLog = self::initAction('update',$projectDir,'SvnAction',$options);
		$configDirectory = '';
		if ( is_null($projectDir) ) {
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
			'revision' 		=> 	null,
			'configDirectory'		=> null,
			'parseResponse' => false
		);
		$options = array_merge($default_options, $options);
		
		// Execute command 
		$projectDir = Utils::formatPath($projectDir);
		$revision = ($options['revision']!=null)?' -r' . $options['revision']:'';
		if (!is_null($options['configDirectory'])) {
			$configDirectory = '--config-dir '.Utils::formatPath( $options['configDirectory'] );
		}
		$command = "svn update --non-interactive $configDirectory $revision $projectDir 2>&1";		
		ShellAction::executeCommand( $command, array('actionLog' => $actionLog) );
		
		// Parse response
		if ( !(strpos( $actionLog->getResult() , 'PROPFIND request failed on' ) === false) && $options['parseResponse'] ) {
			$actionLog->error(__('An error has been detected in the SVN output',true));			
		}
		
		// End action
		$actionLog->end();
		
		return $actionLog;
	}// update
	
}// SvnAction

?>