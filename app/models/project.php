<?php
/* SVN FILE: $Id$ */
/**
 * 
 * 
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @link			http://code.google.com/p/fredistrano
 * @package			app
 * @subpackage		app.models
 * @version			$Revision$
 * @modifiedby		$Author$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * 
 *
 * @package		app
 * @subpackage	app.models
 */
class Project extends AppModel {

	var $name = 'Project';

	var $deploymentMethods = array (
		0 => 'export',
		1 => 'checkout/update'
		);
	
	var $validate = array(
	    'name' => array(
	        'rule1' => array(
	            'rule' => 'alphaNumeric',
	            'required' => true
	        ),
	        'rule2' => array(
	            'rule' => 'isUnique'
	        )
	    ),
		'svn_url' => array(
		        'rule' => 'url', 
		        'required' => true,
		        'allowEmpty' => false
		 ),
		'prd_path' => array(
				'rule' => array('minLength', '1')
		 )
		// 'prd_url' => array(
		//         'rule' => 'url', 
		//         'required' => true,
		//         'allowEmpty' => false
		//  ) 
	);
	
	var $lastReadSize = 0;

	var $lastReadError = 0;

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

	function readAssociatedLog ( $projectId = null, $options = null ) {
		if ( is_null($projectId) || !($project = $this->read(null, $projectId))){
			return false;
		}
		// Init options
		$default_options = array(
			'reverse'			=>	false,
			'pattern' 			=> 	null,
			'logPath'			=> 	null
		);
		$options = array_merge($default_options, $options);		
		
		if (file_exists($options['logPath'])) {	
			// Read log file		
			$file = fopen($options['logPath'],'r');  
			$maxSize = Configure::read('Log.maxSize');
			if ( ($size = filesize ($options['logPath'])) > $maxSize ) {
				fseek( $file, $size - $maxSize);
				$size = $maxSize;
			}
			$this->lastReadSize = $size;
			$output = fread( $file, $size ); 
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
			$this->lastReadSize = 0;
			$this->lastReadError = 'Log not found : File (<em>'.$options['logPath'].'</em>) doesn\'t exist';

			return false;
		}
	}
	
	public function getMethodName($method){
		return $this->deploymentMethods[$method];
	}

}
?>