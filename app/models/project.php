<?php
class Project extends AppModel {

	var $name = 'Project';

//	var $validate = array (
//		'name' => array (
//			array (
//				VALID_NOT_EMPTY,
//				LANG_ENTERPROJECTNAME
//			)
//			,
//			array (array('isUnique', array('name')), LANG_PROJECTNAMEALREADYEXISTS)
//			,
//			array (array('noSpace', array('name')), 'The project name can not contain space')
//		),
//		'svn_url' => array (
//			array (
//				VALID_NOT_EMPTY,
//				LANG_ENTERURLREPOSITORYFORTHISPROJECT
//			)
//		),
//		'prd_url' => array (
//			array (
//				VALID_NOT_EMPTY,
//				LANG_ENTERPRODUCTIONURL
//			)
//		),
//		'prd_path' => array (
//			array (
//				VALID_NOT_EMPTY,
//				LANG_ENTERAPPLICATIONDIRECTORY
//			)
//		)
//	);
	
	var $hasMany = array (
		'DeploymentLog' => array (
			'className' => 'DeploymentLog',
			'order'     => 'DeploymentLog.created DESC',
			'limit'     => '5',
			'foreignKey' => 'project_id',
			'dependent' => false
		)
	);
	
	function noSpace(){
		$tmp = strpbrk($this->data['Project']['name'], " ");
		
		if ($tmp === false)
			return true;
		else
			return false;	
	}
	
	function beforeSave(){
		$this->data['Project']['log_path'] = trim($this->data['Project']['log_path']);
		
		$this->data['Project']['prd_path'] = preg_replace('#[\\\/]#', DS, $this->data['Project']['prd_path']);
		
		if(substr($this->data['Project']['prd_path'], -1, 1) != DS)
			$this->data['Project']['prd_path'] .= DS ;
			
		return true;	
	}


	function readLog ( $projectId = null, $options = null ) {
		if ( is_null($projectId) || !($project = $this->read(null, $projectId))){
			return false;
		}
		// Init options
		$default_options = array(
			'reverse'			=>	false,
			'pattern' 			=> 	null,
			'logPath'			=> $project['Project']['log_path']
		);
		$options = array_merge($default_options, $options);
		
		if (file_exists($options['logPath'])) {	
			// Read log file		
			$file = fopen($options['logPath'],'r');  
			$maxSize = Configure::read('Log.maxSize');
			if ( ($size = filesize ($options['logPath'])) > $maxSize ) {
				fseek( $file, $size - $maxSize);
			}
			$this->lastReadSize = $size;
			$output = fread( $file, $maxSize ); 
			fclose($file);
			
			// Highlight pattern
			if (!is_null($options['pattern'])) {
				$pattern =  ($options['pattern'][0] == '/') ?$options['pattern'] : '/('.$options['pattern'].')/i';
				$output = preg_replace( $pattern , "<span class='highlight'>$1</span>" , $output );
			} 
			
			// Reverse display 
			if ($options['reverse']) {
				$output = array_reverse(explode("\n", $output));
				array_pop($output);
				$output = implode("\n",$output);
			}

			return nl2br($output);
		} else {
			return 'Log not found : File (<em>'.$project['Project']['log_path'].'</em>) doesn\'t exist';
		}
	}

}
?>