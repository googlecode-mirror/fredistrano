<?php 

class LogException extends Exception {
	
	var $log = null;

	public function __construct($message,$log) {
		parent::__construct($message);
		$this->log = $log;
	}// __construct
	
	public function getLog() {
		return $this->log;
	}// __construct
	
}// ActionException

class ElementaryLog {
		
	var $name = null;
		
	var $startTime = null;
	
	var $endTime = null;
	
	var $elapsedTime = 0;
		
	var $error = null;
	
	var $attached = false;
	
	public function __construct($name = null) {
		$this->name = $name;
		$this->startTime = getMicrotime();
	}// __construct
	
	public function end() {
		if (is_null( $this->startTime ) || !is_null($this->endTime)) {
			return false;
		}
		
	 	$this->endTime = getMicrotime();
		$this->elapsedTime = round( ($this->endTime - $this->startTime), 3 );
		return true;
	}// endTimePeriod

	public function error( $error = null, $trigger = true ) {
		// Terminate current element
		$this->end();
		
		// Store exception 
		$this->error = $error;
		
		// Log error in file
		//CakeLog::write(LOG_ERROR, $this->lastError);
		
		// Trigger exception 
		if ($trigger) {
			throw  new LogException( $error, $this );
		}
	}// error

	public function getError() {
		return $this->error; 	
	}// getError

	public function hasError() {
		return !is_null($this->error);
	}// hasError

	public function isAction() {
		return (get_class($this) == 'ActionLog');
	}// isAction
	
	public function isAttached() {
		return $this->attached;
	}// isAttached
	
	public function isEnded() {
		return !is_null($this->endTime);
	}// isEnded
	
	public function markAttached() {
		$this->attached = true;
	}// markAttached
	
	public function toXml() {
		return '';
	}// toXml
		
	public function toString () {
		return 
			'<timePeriod>'
				.'<start timezone="'.date_default_timezone_get().'">'.date(DATE_ATOM, $this->startTime).'</start>'
				.'<end timezone="'.date_default_timezone_get().'">'.date(DATE_ATOM, $this->endTime).'</end>'
				.'<elapsed unit="seconds">'.$this->elapsedTime.'</elapsed>'
			.'</timePeriod>'
			.($this->hasError()?'<error>'.$this->error.'</error>':'');
	}// toString

	public function toHtml () {
		return 
			'<br />[took] '.$this->elapsedTime.' secondes'
			.($this->hasError()?('<br />Error : '.$this->error.'<br />--------------'):'');
	}// toHtml
	
	public function writeToFile( $target ) {
		$log = new File( $target, true );
		if ($log->writable()) {
			return $log->append( $this->toString() );
		}
		return false;
	}// writeToFile
	
}// ElementaryLog

class ActionLog extends ElementaryLog {
	
	var $type = null;	

	var $description = null;
	
	var $command = null;
		
	var $result = null;
	
	public function __construct($name = null, $description = null, $type = null) {
		parent::__construct($name);
		$this->description = $description;		
		$this->type = $type;
	}// __construct
	
	public function end($result = null) {
		parent::end();
		if (!is_null($result)) {
			$this->result = $result;	
		}
	}// endAction
	
	// return the command line output
	public function getResult() {
		return $this->result; 	
	}// getResult
	
	public function saveCommand( $command = null, $result = null ){
		$this->command = $command;
		$this->result = $result; 
	}// setCommand
	
	public function toString () {
		return 
			'<action name="'.$this->name.'" type="'.$this->type.'" >'
				.(!is_null($this->description)?('<description>'.$this->description).'</description>':'')
				.parent::toString()
				.'<job>'
					.((!is_null($this->command))?('<command>'.$this->command.'</command>'):'<command/>')
					.((!is_null($this->result))?('<result>'.$this->result.'</result>'):'<result/>')
				.'</job>'
			.'</action>';
	}// toString

	public function toHtml () {
		return 
			'<div class="actionLog">'
			.'--ACTION------------------------------'
			.'<br />[name] '.$this->name.' [type] '.$this->type
			.(!is_null($this->description)?('<br />[description] '.$this->description):'<br />[description]')
			.parent::toHtml()
			.((!is_null($this->command))?('<br />[command] '.htmlentities($this->command)):'<br />[command]')
			.((!is_null($this->result))?('<br />[result] '.$this->result.'<br />'):'<br />[result]')
			.'</div>';
	}// toHtml
	
}// ActionLog

class AdvancedLog extends ElementaryLog {
	
	var $data = null;
	
	var $logs = array();
	
	var $context = array( 'user' => null, 'uuid'=> null);
	
	var $childType = null;
	
	public function addChildLog( $log, $terminate = false ) {
		if ( get_class($log) != $this->childType ) {
			return false;
		}
		
		// Terminate previous if required 
		$last = $this->getLastLog();
		if (!empty($last)) {
			
			if (!$last->isEnded()) {
				$last->end();
			}
		}
		
		// Terminate if requested
		if (!$log->isEnded() && $terminate) {
			$log->end();
		}
		
		// Add action log
		array_push($this->logs, $log);
		$log->markAttached();
		
		return $log;
	}// addChildLog
	
	public function end() {
		// End current step
		parent::end();
		
		// Log to file if required
		if (isset($this->context['uuid'])) {
			$this->writeToFile( F_DEPLOYLOGDIR.$this->context['uuid'].'.log' );
		}
	}// end
	
	public function error($error = null , $trigger = true ) {
		if (is_null($error)) {
			$error = $this->getLastError();
		}
		parent::error($error,$trigger);
	}// error

	public function getLastError(){	
		if (!is_null($this->error)) {
			return $this->error;
		}
		
		$count = count($this->logs);
		for ( $i = 1 ; $i <= $count ; $i++ ) {
			$log = $this->logs[$count - $i];
			if ($log->hasError()) {
				return '[Action: '.$log->name.'] > '.$log->getError();
			}
		}
		return false;
	}// getLastError

	public function getLastLog() {
		$ret = false;
		if ( ( $size = count($this->logs) ) == 0 ) {
			return $ret;
		} 
		
		return $this->logs[$size-1];
	}// getLastLog
	
	public function hasError($recursive = false) {
		if (!is_null($this->error)) {
			return true;
		} else if ($recursive) {
			foreach ($this->logs as $log) {
				if ( $log->hasError() ) {
					return true;
				}
			}
			return false;

		} else {
			return false;
		}
	}// hasError
	
	public function setContext($context) {
		if (!is_array($context)) {
			return false;
		}
		$this->context = array_merge($this->context, $context);
	}// setContext
	
}// AdvancedLog

class StepLog extends AdvancedLog {
	
	var $childType = 'ActionLog';
	
	public function addNewAction( $name = null, $description = null, $type = null ) {
		$actionLog =  new ActionLog($name, $description, $type);
		return $this->addChildLog( $actionLog );
	}// addNewAction
	
	public function toString( $showContext=true ) {
		$actionLogs = '';
		foreach($this->logs as $actionLog) {
			$actionLogs .= $actionLog->toString();
		}
		
		if ($showContext) {
			$uuid = (!is_null($this->context['uuid']))?'uuid=""':'';
			$user = $this->context['user'];
		} else {
			$uuid = '';
			$user = '';	
		}
		return 
			'<step name="'.$this->name."\" $uuid>"
				.parent::toString()
				.$user
				.'<actions>'.$actionLogs.'</actions>'
			.'</step>';
	}// toString

	public function toHtml( $showContext=true ) {
		$actionLogs = '';
		foreach($this->logs as $actionLog) {
			$actionLogs .= $actionLog->toHtml();
		}
		
		if ($showContext) {
			$uuid = (!is_null($this->context['uuid']))?'uuid='.$this->context['uuid']:'';
			$user = $this->context['user'];
		} else {
			$uuid = '';
			$user = '';	
		}
		return 
			'<div class="stepLog">'
			.'<br />--STEP--------------------------------'
			.'<br />[step name] '.$this->name." \ ".$uuid
			.parent::toHtml()
			.'<br />[user] '.$user
			.'<br />'.$actionLogs
			.'</div>';
	}// toHtml
	
}// StepLog

class Processlog extends AdvancedLog {

	var $childType = 'StepLog';

	public function toString() {
		
		$stepLogs = '';
		foreach($this->logs as $stepLog) {
			$stepLogs .= $stepLog->toString(false);
		}
		return 
			'<process uuid="'.$this->context['uuid'].'">'
				.parent::toString()
				.'<user>'.$this->context['user'].'</user>'
				.'<steps>'.$stepLogs.'</steps>'
			.'</process>';
	}// toString

	public function toHtml() {
		
		$stepLogs = '';
		foreach($this->logs as $stepLog) {
			$stepLogs .= $stepLog->toHtml(false);
		}
		return 
			'<br />======================================='
			.'<br />[process uuid] '.$this->context['uuid']
				.parent::toHtml()
				.'<br />[user] '.$this->context['user']
				.'<br />[steps] '.$stepLogs
			.'<br />===';
	}// toHtml

}// Processlog
?>
