<?php
namespace Asgard\Validation\Rules;

/**
 * Check that each element of the iterable input validates the given validator.
 */
class Each extends \Asgard\Validation\Rule {
	/**
	 * Validator.
	 * @var \Asgard\Validation\Validator
	 */
	public $validator;

	/**
	 * Constructor.
	 * @param \Asgard\Validation\Validator $validator
	 */
	public function __construct(\Asgard\Validation\Validator $validator) {
		$this->validator = $validator;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		foreach($input as $k=>$v) {
			if($this->validator->valid($v) === false)
				return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute is invalid.';
	}
}