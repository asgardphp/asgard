<?php
namespace Asgard\Validation\Rules;

class Same extends Rule {
	protected $as;

	public function __construct($as) {
		$this->as = $as;
	}

	public function validate($input, $parentInput, $validator) {
		// d($parentInput, $this->as, $parentInput->attribute($this->as)->input());
		// d($input, $parentInput->attribute($this->as)->input());
		return $input == $parentInput->attribute($this->as)->input();
	}

	public function getMessage() {
		return ':attribute must be same as '.explode('.', $this->as)[sizeof($this->as)-1].'.';
	}
}