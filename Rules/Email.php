<?php
namespace Asgard\Validation\Rules;

class Email extends Rule {
	public function validate($input, $parentInput, $validator) {
		return filter_var($input, FILTER_VALIDATE_EMAIL);
	}

	public function getMessage() {
		return ':attribute must be a valid email address.';
	}
}