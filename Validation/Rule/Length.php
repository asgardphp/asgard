<?php
namespace Asgard\Validation\Rule;

/**
 * Check that the input length is equal to a given number.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Length extends \Asgard\Validation\Rule {
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
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		return strlen($input) === $this->length;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must be :length characters long.';
	}
}