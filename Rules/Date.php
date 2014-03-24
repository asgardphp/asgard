<?php
namespace Asgard\Validation\Rules;

class Date extends Rule {
	public function validate($input, $parentInput, $validator) {
		return preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $input) === 1;
	}

	public function getMessage() {
		return ':attribute must be a date (dd/mm/yyyy).';
	}
}