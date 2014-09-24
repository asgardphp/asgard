<?php
namespace Asgard\Validation\Rules;

/**
 * Check that the input is equal or greater than the given number.
 */
class Min extends \Asgard\Validation\Rule {
	/**
	 * Minimum number.
	 * @var float
	 */
	public $min;

	/**
	 * Constructor.
	 * @param float $min
	 */
	public function __construct($min) {
		$this->min = $min;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return $input >= $this->min;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must be greater than :min.';
	}
}