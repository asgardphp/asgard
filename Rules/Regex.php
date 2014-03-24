<?php
namespace Asgard\Validation\Rules;

class Regex extends Rule {
	public $pattern;

	public function __construct($pattern) {
		$this->pattern = $pattern;
	}

	public function validate($input, $parentInput, $validator) {
		return preg_match($this->pattern, $input) === 1;
	}

	public function getMessage() {
		return ':attribute must match pattern ":pattern".';
	}
}