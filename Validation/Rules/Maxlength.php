<?php
namespace Asgard\Validation\Rules;

/**
 * Check that the input length is less or equal to the given length.
 */
class Maxlength extends \Asgard\Validation\Rule {
	/**
	 * Length.
	 * @var integer
	 */
	public $length;

	/**
	 * Constructor.
	 * @param integer $length
	 */
	public function __construct($length) {
		$this->length = $length;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return strlen($input) <= $this->length;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must be at most :length characters long.';
	}
}