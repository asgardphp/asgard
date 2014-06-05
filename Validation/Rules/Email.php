<?php
namespace Asgard\Validation\Rules;

class Email extends \Asgard\Validation\Rule {
	protected $handleEach = true;
	
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return filter_var($input, FILTER_VALIDATE_EMAIL);
	}

	public function getMessage() {
		return ':attribute must be a valid email address.';
	}
}