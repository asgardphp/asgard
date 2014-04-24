<?php
namespace Asgard\Orm\Validation;

class Relationrequired extends \Asgard\Validation\Rule {
	public function validate($input) {
		return $input->count() > 0;
	}

	public function getMessage() {
		return ':attribute is required.';
	}
}