<?php
namespace Asgard\Validation;

/**
 * Validator exception.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ValidatorException extends \Exception {
	/**
	 * Errors report.
	 * @var Report
	 */
	protected $report;

	/**
	 * Constructor.
	 * @param string $message
	 * @param Report $report
	 */
	public function __construct($message=null, Report $report=null) {
		$this->report = $report;
		parent::__construct($message);
	}

	public function setReport($report) {
		$this->report = $report;
		return $this;
	}

	/**
	 * Returnt the report
	 * @return Report
	 */
	public function report() {
		return $this->report;
	}
}