<?php
namespace Asgard\Validation\Rules;

/**
 * Check that the input length is between two given numbers.
 */
class Lengthbetween extends \Asgard\Validation\Rule {
	/**
	 * Min length.
	 * @var integer
	 */
	public $min;
	/**
	 * Max length.
	 * @var integer
	 */
	public $max;

	/**
	 * Constructor.
	 * @param integer $min
	 * @param integer $max
	 */
	public function __construct($min, $max) {
		$this->min = $min;
		$this->max = $max;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		if($this->min !== null && strlen($input) <= $this->min)
			return false;
		if($this->max !== null && strlen($input) >= $this->max)
			return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		if($this->min !== null && $this->max !== null)
			return ':attribute must be between :min and :max characters long.';
		elseif($this->min !== null)
			return ':attribute must be more than :min characters long.';
		elseif($this->max !== null)
			return ':attribute must be less than :max characters long.';
	}
}