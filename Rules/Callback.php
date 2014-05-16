<?php
namespace Asgard\Validation\Rules;

class Callback extends \Asgard\Validation\Rule {
	public $cb;

	public function __construct($cb) {
		$this->cb = $cb;
	}

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return call_user_func_array($this->cb, array($input, $parentInput, $validator));
	}

	public function getMessage() {
		return ':attribute is invalid.';
	}
}