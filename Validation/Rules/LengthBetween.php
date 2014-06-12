<?php
namespace Asgard\Validation\Rules;

class LengthBetween extends \Asgard\Validation\Rule {
	public $min;
	public $max;

	public function __construct($min, $max) {
		$this->min = $min;
		$this->max = $max;
	}

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		if($this->min !== null && strlen($input) <= $this->min)
			return false;
		if($this->max !== null && strlen($input) >= $this->max)
			return false;
	}

	public function getMessage() {
		if($this->min !== null && $this->max !== null)
			return ':attribute must be between :min and :max characters long.';
		elseif($this->min !== null)
			return ':attribute must be more than :min characters long.';
		elseif($this->max !== null)
			return ':attribute must be less than :max characters long.';
	}
}