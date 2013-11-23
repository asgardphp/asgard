<?php
namespace Coxis\Core;

class PSRException extends \Exception {
	protected $severity;

	function __construct($severity=null, $message=null) {
		$this->severity = $severity;
		parent::__construct($message);
	}

	public function setSeverity($severity) {
		$this->severity = $severity;
	}

	public function getSeverity() {
		return $this->severity;
	}

	public function getMessage() {
		return get_class($this).': '.$this->getMessage();
	}
}