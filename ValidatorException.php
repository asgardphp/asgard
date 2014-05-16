<?php
namespace Asgard\Validation;

class ValidatorException extends \Exception {
	protected $report;

	public function __construct($message, Report $report) {
		$this->report = $report;
		parent::__construct($message);
	}

	public function errors() {
		return $this->report;
	}
}