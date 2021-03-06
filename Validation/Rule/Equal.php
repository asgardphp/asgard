<?php
namespace Asgard\Validation\Rule;

/**
 * Check that the input is equal to a given value.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Equal extends \Asgard\Validation\Rule {
	/**
	 * Value.
	 * @var mixed
	 */
	public $equal;

	/**
	 * Constructor.
	 * @param mixed $equal
	 */
	public function __construct($equal) {
		$this->equal = $equal;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		return $input == $this->equal;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must be equal to :equal.';
	}
}