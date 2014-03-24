<?php
namespace Asgard\Validation\Rules;

class Min extends Rule {
	public $min;

	public function __construct($min) {
		$this->min = $min;
	}

	public function validate($input, $parentInput, $validator) {
		return $input >= $this->min;
	}

	public function getMessage() {
		return ':attribute must be greater than :min.';
	}
}