<?php
namespace Asgard\Validation\Rules;

/**
 * Check that the string length is greater or equal to the given length.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Minlength extends \Asgard\Validation\Rule {
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
		return strlen($input) >= $this->length;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must be at least :length characters long.';
	}
}