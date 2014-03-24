<?php
namespace Asgard\Validation\Rules;

class Any extends Rule {
	public $rules;

	public function __construct() {
		$this->rules = func_get_args();
	}

	public function validate($input, $parentInput, $validator) {
		foreach($this->rules as $rule) {
			if($rule instanceof \Asgard\Validation\Validator) {
				if($rule->valid($input) !== false)
					return true;
			}
		}
		return false;
	}

	public function getMessage() {
		return ':attribute is invalid.';
	}
}