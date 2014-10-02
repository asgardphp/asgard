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
	public function __construct($message, Report $report) {
		$this->report = $report;
		parent::__construct($message);
	}

	/**
	 * Returnt the report
	 * @return Report
	 */
	public function errors() {
		return $this->report;
	}
}