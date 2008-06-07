<?php
uses('file');
class LogsController extends AppController {
	
	var $name = 'Logs';
	
	var $uses = array('Project');
	
	var $helpers = array (
		'Html',
		'Form',
		'Ajax',
		'Pagination'
	);
	
	var $authLocal = array (
		'Logs'	=> 	array( 'entrance' )
	);
	
	function beforeRender() {
		parent::beforeRender();
		
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array (
			'text' => 'Actions'
		);

		$tab[] = array (
			'text' => 'List projects',
			'link' => '/projects'
		);
		$this->set("context_menu", $tab);
	}
	
	function index($project_id = '') {
		$projects = $this->Project->find( 'list', array("`Project`.`log_path` != 'NULL'", "`Project`.`log_path` != ''"),'Project.name ASC');
		
		$this->set('project_id', $project_id);
		$this->set('projects', $projects);
	}// index
	
	function view() {
		$this->layout = 'ajax';
		if (!empty($this->data) && !empty($this->data['Search']['project_id'])) {
			
			$project = $this->Project->read(null, $this->data['Search']['project_id']);
			
			if ($project['Project']['name'] == '#WebAS') {
				if ($handle = opendir($project['Project']['log_path'])) {
					$tmpLog = "error.log.0";
					while (false !== ($filename = readdir($handle))) {
        				if	( strpos($filename, 'error.log.') !== false && strcasecmp($filename,$tmpLog) > 0) {
        					$tmpLog = $filename;
				     	}
				 	}
				}
			    closedir($handle);
				$project['Project']['log_path'] = $project['Project']['log_path'].$tmpLog;
			}
		
			if (file_exists($project['Project']['log_path'])) {
				
				$file = fopen($project['Project']['log_path'],'r');  
				$maxsize =(!empty($this->data['Search']['maxsize']))?$this->data['Search']['maxsize']:_MAXLOGSIZE;
				if ( ($size = filesize ($project['Project']['log_path'])) > $maxsize ) {
					fseek( $file, $size - $maxsize);
				}
				$this->set('size', $size);
				$output = fread( $file, $maxsize ); 
				fclose($file);
				
				if (!empty($this->data['Search']['pattern'])) {
					$pattern =  ($this->data['Search']['pattern'][0] == '/') ? $this->data['Search']['pattern'] : '/('.$this->data['Search']['pattern'].')/i';
					$output = preg_replace( $pattern , "<span class='highlight'>$1</span>" , $output );
				} 
				
				if ($this->data['Search']['reverse']) {
					$output = array_reverse(explode("\n", $output));
					array_pop($output);
					$output = implode("\n",$output);
				}
								
				$output = nl2br($output);
			} else {
				$output = 'Log not found : File (<em>'.$project['Project']['log_path'].'</em>) doesn\'t exist';
			}
			$this->set('project', $project);
			$this->set('log',$output);
		} else {
			die();
		}
	}// view
	
}// Logs
?>