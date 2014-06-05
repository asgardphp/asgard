<?php
namespace Asgard\Validation\Rules;

class Each extends \Asgard\Validation\Rule {
	public $validator;

	public function __construct(\Asgard\Validation\Validator $validator) {
		$this->validator = $validator;
	}

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		foreach($input as $k=>$v) {
			if($this->validator->valid($v) === false)
				return false;
		}

		return true;
	}

	public function getMessage() {
		return ':attribute is invalid.';
	}
}