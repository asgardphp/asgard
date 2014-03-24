<?php
namespace Asgard\Validation\Rules;

class Int extends Rule {
	public function validate($input, $parentInput, $validator) {
		return is_int($input);
	}

	public function getMessage() {
		return ':attribute must be an integer.';
	}
}