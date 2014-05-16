<?php
namespace Asgard\Validation\Rules;

class Equal extends \Asgard\Validation\Rule {
	public $equal;

	public function __construct($equal) {
		$this->equal = $equal;
	}

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return $input == $this->equal;
	}

	public function getMessage() {
		return ':attribute must be equal to :equal.';
	}
}