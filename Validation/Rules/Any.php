<?php
namespace Asgard\Validation\Rules;

/**
 * Check if any of the rule validates the input.
 */
class Any extends \Asgard\Validation\Rule {
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
				if($rule->valid($input) !== false)
					return true;
			}
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