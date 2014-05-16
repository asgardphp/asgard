<?php
namespace Asgard\Validation\Rules;

class Regex extends \Asgard\Validation\Rule {
	public $pattern;

	public function __construct($pattern) {
		$this->pattern = $pattern;
	}

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return preg_match($this->pattern, $input) === 1;
	}

	public function getMessage() {
		return ':attribute must match pattern ":pattern".';
	}
}