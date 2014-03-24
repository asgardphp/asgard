<?php
namespace Asgard\Validation\Rules;

class Contains extends Rule {
	public function __construct($contain) {
		$this->contain = $contain;
	}

	public function validate($input, $parentInput, $validator) {
		return strpos($input, $this->contain) !== false;
	}

	public function getMessage() {
		return ':attribute must contain ":contain".';
	}
}