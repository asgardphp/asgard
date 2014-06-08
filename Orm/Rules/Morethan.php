<?php
namespace Asgard\Orm\Validation;

class Morethan extends \Asgard\Validation\Rule {
	public $more;

	public function __construct($more) {
		$this->more = $more;
	}

	public function validate($input) {
		return $input->count() > $this->more;
	}

	public function getMessage() {
		return ':attribute must have more than :more elements.';
	}
}