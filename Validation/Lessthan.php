<?php
namespace Asgard\Orm\Validation;

class Lessthan extends \Asgard\Validation\Rule {
	public $less;

	public function __construct($less) {
		$this->less = $less;
	}

	public function validate($input) {
		return $input->count() < $this->less;
	}

	public function getMessage() {
		return ':attribute must have less than :less elements.';
	}
}