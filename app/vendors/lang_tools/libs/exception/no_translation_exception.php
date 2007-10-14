<?php

/**
 * Custom exception 
 * 
 * @filesource
 * @link			http://blog.fbollon.net
 * @package			fbc.lang
 * @author 			euphrate_ylb
 */

class NoTranslationException extends Exception {

	// Redefine the exception so message isn't optional
	public function __construct($message, $code = 0) {
		// some code

		// make sure everything is assigned properly
		parent :: __construct($message, $code);
	}

	// custom string representation of object
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}

} // InvalidArgException