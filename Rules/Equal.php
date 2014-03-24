<?php
namespace Asgard\Validation\Rules;

class Equal extends Rule {
	public $equal;

	public function __construct($equal) {
		$this->equal = $equal;
	}

	public function validate($input, $parentInput, $validator) {
		return $input == $this->equal;
	}

	public function getMessage() {
		return ':attribute must be equal to :equal.';
	}
}