<?php
namespace Asgard\Validation\Rules;

class Isinstanceof extends \Asgard\Validation\Rule {
	public function __construct($class) {
		$this->class = $class;
	}

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return $input instanceof $this->class;
	}

	public function getMessage() {
		return ':attribute must be an instance of ":class".';
	}
}