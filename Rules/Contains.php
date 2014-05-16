<?php
namespace Asgard\Validation\Rules;

class Contains extends \Asgard\Validation\Rule {
	public function __construct($contain) {
		$this->contain = $contain;
	}

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return strpos($input, $this->contain) !== false;
	}

	public function getMessage() {
		return ':attribute must contain ":contain".';
	}
}