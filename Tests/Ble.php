<?php
namespace Asgard\Validation\Tests;

class Ble extends \Asgard\Validation\Rule {
	protected $equal;

	public function __construct($equal) {
		$this->equal = $equal;
	}

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return $input === $this->equal;
	}
}