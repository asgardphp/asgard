<?php
namespace Asgard\Files\Rules;

class Filerequired extends \Asgard\Validation\Rule {
	public function validate($input, $parentInput, $validator) {
		if(!$input instanceof \Asgard\Files\EntityFile)
			return false;
		return $input->exists();
	}

	public function getMessage() {
		return 'The file :attribute is required.';
	}
}