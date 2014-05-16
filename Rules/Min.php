<?php
namespace Asgard\Validation\Rules;

class Min extends \Asgard\Validation\Rule {
	public $min;

	public function __construct($min) {
		$this->min = $min;
	}

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return $input >= $this->min;
	}

	public function getMessage() {
		return ':attribute must be greater than :min.';
	}
}