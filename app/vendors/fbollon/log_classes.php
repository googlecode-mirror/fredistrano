<?php 

// Debug scenario
if ( Configure::read() > 0 ) {
	if (!class_exists('CakeLog')) {
		uses('cake_log');	
	}			
}

class ElementaryLog {
	
	public $data = null;
	
	private $name = null;
	
	private $description = null;
	
	private $startTime = null;
	
	private $endTime = null;
	
	private $elapsedTime = 0;
		
	private $error = null;
	
	public function __construct($name = null, $description = null) {
		$this->name = $name;
		$this->description = $description;
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

	public function getLastStep() {
		return $this->error; 	
	}// getError

	public function hasError() {
		return !is_null($this->error);
	}// hasError
	
	public function isEnded() {
		return !is_null($this->endTime);
	}// isEnded
	
	public function error( $error, $trigger = true ) {
		// Terminate current element
		$this->end();
		
		// Store exception 
		$this->error = new Exception( $error );
		
		// Log error in file
		//CakeLog::write(LOG_ERROR, $this->lastError);
				
		// Trigger exception 
		if ($trigger) {
			throw $this->error;
		}
	}// error
	
	public function toXml() {
		return null;		
	}// toXml
	
	public function toString () {
		return 
			'<description>'.$this->description.'</description>'
			.'<timePeriod>'
				.'<start timezone="'.date_default_timezone_get().'">'.date(DATE_ATOM, $this->startTime).'</start>'
				.'<end timezone="'.date_default_timezone_get().'">'.date(DATE_ATOM, $this->endTime).'</end>'
				.'<elapsed unit="seconds">'.$this->elapsedTime.'</elapsed>'
			.'</timePeriod>'
			.(!$this->hasError()?:'<error>'.$this->error.'</error>':'');
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

	private $command = null;
		
	private $result = null;
	
	public function __construct($name = null, $description = null, $type = null) {
		parent::__construct($name, $description);
		$this->type = $type;
	}// __construct

	public function setCommand($command = null ){
		$this->command = $command;
		$this->type = 'shell'; 
	}// setCommand
	
	public function end($result = null) {
		parent::end();
		$this->result = $result;
	}// endAction
	
	public function toXml() {
		return null;		
	}// toXml
	
	public function toString () {
		return 
			'<action name="'.$this->name.'" type="'.$this->type.'" >'
				.parent::toString()
				.'<job>'
					.(!is_null($this->command)?:'<command>'.$this->command.'</command>':'<command/>')
					.(!is_null($this->result)?:'<result>'.$this->result.'</result>':'<result/>')
				.'</job>'
			.'</action>';
	}// toString
	
}// ActionLog

class Steplog extends ElementaryLog {
		
	private $uuid = null;

	private $user = null;
		
	private $actionLogs = array();

	public function addAction( $actionLog, $terminate = false ) {
		if ( get_class($actionLog) != 'ActionLog' ) {
			return false;
		}
		
		// Terminate previous if required 
		if (!is_null( $last = $this->getLastAction() ) ) {
			if (!$last->isEnded()) {
				$last->end();
			}
		}
		
		// Terminate if requested
		if (!$actionLog->isEnded() && $terminate) {
			$actionLog->end();
		}
		
		// Add action log
		array_push($this->actionLogs, $actionLog);

		return $actionLog;
	}// logNextAction
	
	public function addNewAction( $name = null, $description = null, $type = null ) {
		$actionLog = new ActionLog($name, $description, $type);
		return $this->addAction($actionLog);
	}// addNewAction
	
	public function end() {
		// End current step
		parent::end();
		
		// Log to file if required
		if (!is_null($this->uuid)) {
			$this->writeToFile( F_DEPLOYLOGDIR.$this->uuid.'.log' );			
		}
	}// end
	
	public function getLastAction() {
		if ( ( $size = count($this->actionLogs) ) == 0 ) ) {
			return false;
		}
		
		return $this->actionLogs[$size-1]; 	
	}// getLastAction
	
	public function getResult() {
		return $this->result; 	
	}// getLastAction
	
	// public function getLastError() {
	// 
	// }// getLastError
	
	public function hasError($recursive = false) {
		if (!is_null($this->error)) {
			return true;
		} else if ($recursive) {
			foreach ($this->actionLogs as $actionLog) {
				if ( $actionLog->hasError() ) {
					return true;
				}
			}
			return false;

		} else {
			return false;
		}
	}// hasError
	
	public function setContext($user, $uuid) {
		$this->user = $user;
		$this->uuid = $uuid;
	}// setContext
	
	public function toString( $showContext=true ) {
		$actionLogs = '';
		foreach($this->actionLogs as $actionLog) {
			$actionLogs .= $actionLog->toString();
		}
		
		$context = '';
		if ($showContext) {
			$context = 
				'<context>'
					.'<user>'.$this->startTime.'</user>'
					.'<uuid>'.$this->endTime.'</uuid>'
				.'</context>';
		}
		return 
			'<step name="'.$this->name.'">'
				.parent::toString()
				.$context
				.'<actions>'.$actionLogs.'</action>'
			.'</step>';
	}// toString
	
}// Steplog

class Processlog extends ElementaryLog {
		
	private $uuid = null;

	private $user = null;
		
	private $stepLogs = array();

	public function addStep( $stepLog, $terminate = false ) {
		if ( get_class($stepLog) != 'StepLog' ) {
			return false;
		}
		
		// Terminate previous if required 
		if (!is_null( $last = $this->getLastStep() ) ) {
			if (!$actionLog->isEnded()) {
				$last->end();
			}
		}
		
		// Terminate if requested
		if (!$actionLog->isEnded() && $terminate) {
			$stepLog->end();
		}
		
		// Add action log
		array_push($this->stepLogs, $stepLog);

		return $stepLog;	
	}// addStep 
	
	public function getLastStep() {
		if ( ( $size = count($this->stepLogs) ) == 0 ) ) {
			return false;
		} 
		
		return $this->stepLogs[$size-1]; 	
	}// getLastStep
	
	public function end() {
		// End current step
		parent::end();
		
		// Log to file if required
		if (!is_null($this->uuid)) {
			$this->writeToFile( F_DEPLOYLOGDIR.$this->uuid.'.log' );			
		}
	}// end
	
	public function hasError($recursive = false) {
		if (!is_null($this->error)) {
			return true;
		} else if ($recursive) {
			foreach ($this->stepLogs as $stepLog) {
				if ( $stepLog->hasError() ) {
					return true;
				}
			}
			return false;

		} else {
			return false;
		}
	}// hasError
	
	public function setContext($user, $uuid) {
		$this->user = $user;
		$this->uuid = $uuid;
	}// setContext
	
	
	public function toString() {
		$stepLogs = '';
		foreach($this->stepLogs as $stepLog) {
			$stepLogs .= $stepLog->toString(false);
		}
		return 
			'<process uuid="'.$this->uuid.'">'
				.parent::toString()
				.'<user>'.$this->startTime.'</user>'
				.'<steps>'.$stepLogs.'</steps>'
			.'</process>';
	}// toString

}// Processlog
?>