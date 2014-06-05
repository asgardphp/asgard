<?php
namespace Asgard\Validation\Rules;

class Date extends \Asgard\Validation\Rule {
	protected $handleEach = true;

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		if($input instanceof \Carbon\Carbon)
			return true;
		if(is_string($input))
			return preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $input) === 1;
		return false;
	}

	public function getMessage() {
		return ':attribute must be a date (yyyy-mm-dd).';
	}
}