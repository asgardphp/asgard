<?php
namespace Asgard\Validation\Rules;

class Same extends \Asgard\Validation\Rule {
	protected $as;

	public function __construct($as) {
		$this->as = $as;
	}

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return $input == $parentInput->attribute($this->as)->input();
	}

	public function getMessage() {
		return ':attribute must be same as '.explode('.', $this->as)[count($this->as)-1].'.';
	}
}