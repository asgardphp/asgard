<?php
namespace Asgard\Validation\Rules;

class Int extends \Asgard\Validation\Rule {
	public function validate($input, $parentInput, $validator) {
		return is_int($input);
	}

	public function getMessage() {
		return ':attribute must be an integer.';
	}
}