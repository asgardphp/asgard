<?php
namespace Asgard\Validation\Rules;

class Length extends Rule {
	public $length;

	public function __construct($length) {
		$this->length = $length;
	}

	public function validate($input, $parentInput, $validator) {
		return strlen($input) === $this->length;
	}

	public function getMessage() {
		return ':attribute must be :length characters long.';
	}
}