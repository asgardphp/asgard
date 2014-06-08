<?php
namespace Asgard\Validation\Rules;

class Hasmorethan extends \Asgard\Validation\Rule {
	protected $count;

	public function __construct($count) {
		$this->count = $count;
	}

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return count($input) > $count;
	}

	public function getMessage() {
		return ':attribute must have more than :count elements.';
	}
}