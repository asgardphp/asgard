<?php
namespace Asgard\Validation\Rules;

/**
 * Check if any of the validator validates the input.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Any extends \Asgard\Validation\Rule {
	/**
	 * Validators.
	 * @var array
	 */
	public $validators;

	/**
	 * {@inheritDoc}
	 */
	public function __construct() {
		$this->validators = func_get_args();
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		foreach($this->validators as $name=>$_validator) {
			if($_validator->valid($input) !== false)
				return true;
		}
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute is invalid.';
	}
}