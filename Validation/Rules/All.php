<?php
namespace Asgard\Validation\Rules;

/**
 * Check if all the rules validates the input.
 */
class All extends \Asgard\Validation\Rule {
	/**
	 * Rules.
	 * @var array
	 */
	public $rules;

	/**
	 * {@inheritDoc}
	 */
	public function __construct() {
		$this->rules = func_get_args();
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		foreach($this->rules as $rule) {
			if($rule instanceof \Asgard\Validation\Validator) {
				if($rule->valid($input) === false)
					return false;
			}
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