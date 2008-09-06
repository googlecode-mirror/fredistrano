<?php 

class LogException extends Exception {
	
	private $log = null;

	public function __construct($message,$log) {
		parent::__construct($message);
		$this->log = $log;
	}// __construct
	
	public function getLog() {
		return $this->log;
	}// __construct
	
}// ActionException

class ElementaryLog {
		
	protected $name = null;
		
	protected $startTime = null;
	
	protected $endTime = null;
	
	protected $elapsedTime = 0;
		
	protected $error = null;
	
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

	public function getError() {
		return $this->error; 	
	}// getError

	public function hasError() {
		return !is_null($this->error);
	}// hasError

	public function isAction() {
		return (get_class($this) == 'ActionLog');
	}// isAction
	
	public function isEnded() {
		return !is_null($this->endTime);
	}// isEnded
		
	public function error( $error, $trigger = true ) {
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
	
	public function writeToFile( $target ) {
		$log = new File( $target, true );
		if ($log->writable()) {
			return $log->append( $this->toString() );
		}
		return false;
	}// writeToFile
	
}// ElementaryLog

class ActionLog extends ElementaryLog {
	
	private $type = null;	

	private $description = null;
	
	private $command = null;
		
	private $result = null;
	
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
				.(!is_null($this->description)?'<description>'.$this->description.'</description>':'')
				.parent::toString()
				.'<job>'
					.((!is_null($this->command))?('<command>'.$this->command.'</command>'):'<command/>')
					.((!is_null($this->result))?('<result>'.$this->result.'</result>'):'<result/>')
				.'</job>'
			.'</action>';		
	}// toString
	
}// ActionLog

class AdvancedLog extends ElementaryLog {
	
	public $data = null;
	
	protected $logs = array();
	
	protected $context = array( 'user' => null, 'uuid'=> null);
	
	protected $childType = null;
	
	public function addChildLog( $log, $terminate = false ) {
		if ( get_class($log) != $this->childType ) {
			return false;
		}
		
		// Terminate previous if required 
		$last = $this->getLastLog();
		if ( !empty( $last ) ) {
			
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

		return $log;	
	}// addChildLog
	
	public function end() {
		// End current step
		parent::end();
		
		// Log to file if required
		if (!is_null($this->context['uuid'])) {
			$this->writeToFile( F_DEPLOYLOGDIR.$this->context['uuid'].'.log' );			
		}
	}// end

	public function getLastLog() {
		if ( ( $size = count($this->logs) ) == 0 ) {
			return false;
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
		if (is_array($context)) {
			return false;
		}
		$this->context = array_merge($this->context, $context);
	}// setContext
	
}// AdvancedLog

class Steplog extends AdvancedLog {
	
	protected $childType = 'ActionLog';
	
	public function addNewAction( $name = null, $description = null, $type = null ) {
		$actionLog = new ActionLog($name, $description, $type);
		return $this->addChildLog( $actionLog );
	}// addNewAction
	
	public function toString( $showContext=true ) {
		$actionLogs = '';
		foreach($this->logs as $actionLog) {
			$actionLogs .= $actionLog->toString();
		}
		
		$context = '';
		if ($showContext) {
			$uuid = (!is_null($this->context['uuid']))?'uuid=""':'';
			$user = $this->context['user'];
		}
		return 
			'<step name="'.$this->name."\" $uuid>"
				.parent::toString()
				.$user
				.'<actions>'.$actionLogs.'</actions>'
			.'</step>';
	}// toString
	
}// Steplog

class Processlog extends AdvancedLog {

	protected $childType = 'StepLog';

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

}// Processlog
?>
