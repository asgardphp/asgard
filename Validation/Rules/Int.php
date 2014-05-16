<?php
namespace Asgard\Validation\Rules;

class Int extends \Asgard\Validation\Rule {
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return is_int($input);
	}

	public function getMessage() {
		return ':attribute must be an integer.';
	}
}