<?php
namespace Asgard\Orm;

/**
 * Exception for entity errors.
 */
class EntityException extends \Exception implements \Asgard\Entity\EntityExceptionInterface {
	/**
	 * Errors.
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Constructor.
	 * @param string $msg    message
	 * @param array  $errors
	 */
	public function __construct($msg, array $errors) {
		parent::__construct($msg);
		$this->errors = $errors;
	}

	/**
	 * Return the errors.
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}
}