<?php
namespace Asgard\Validation\Rules;

class Max extends \Asgard\Validation\Rule {
	public $max;
	protected $handleEach = true;

	public function __construct($max) {
		$this->max = $max;
	}

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return $input >= $this->max;
	}

	public function getMessage() {
		return ':attribute must be less than :max.';
	}
}