<?php
namespace Asgard\Core;

class PSRException extends \Exception {
	protected $severity;

	function __construct($message=null, $severity=null) {
		if($severity === null)
			$severity = \Psr\Log\LogLevel::ERROR;
		$this->severity = $severity;
		parent::__construct($message);
	}

	public function setSeverity($severity) {
		$this->severity = $severity;
	}

	public function getSeverity() {
		return $this->severity;
	}

	// public function getMessage() {
	// 	return get_class($this).': '.$this->getMessage();
	// }
}