<?php
namespace Asgard\Validation\Rules;

/**
 * Return the opposite result of a validator.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Not extends \Asgard\Validation\Rule {
	/**
	 * Validator.
	 * @var array
	 */
	public $validator;

	/**
	 * {@inheritDoc}
	 */
	public function __construct($validator) {
		$this->validator = $validator;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		return !$this->validator->valid($input);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute is invalid.';
	}
}