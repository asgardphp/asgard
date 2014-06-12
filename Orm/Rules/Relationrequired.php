<?php
namespace Asgard\Orm\Rules;

class Relationrequired extends \Asgard\Validation\Rule {
	public function validate($input) {
		return $input->count() > 0;
	}

	public function getMessage() {
		return ':attribute is required.';
	}
}