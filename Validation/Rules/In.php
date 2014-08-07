<?php
namespace Asgard\Validation\Rules;

class In extends \Asgard\Validation\Rule {
	public $in;

	public function __construct($in) {
		$this->in = $in;
	}

	public function validate($input) {
		return in_array($input, $this->in);
	}

	public function getMessage() {
		return ':attribute is invalid.';
	}
}