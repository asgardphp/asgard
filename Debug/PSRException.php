<?php
namespace Asgard\Debug;

/**
 * Exception for PSR-3.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class PSRException extends \Exception {
	/**
	 * Error severity.
	 * @var integer
	 */
	protected $severity;

	/**
	 * Constructor.
	 * @param string $message
	 * @param integer $severity
	 */
	public function __construct($message=null, $severity=null) {
		if($severity === null)
			$severity = \Psr\Log\LogLevel::ERROR;
		$this->severity = $severity;
		parent::__construct($message);
	}

	/**
	 * Set the severity.
	 * @param integer $severity
	 */
	public function setSeverity($severity) {
		$this->severity = $severity;
	}

	/**
	 * Get the severity.
	 * @return integer
	 */
	public function getSeverity() {
		return $this->severity;
	}
}