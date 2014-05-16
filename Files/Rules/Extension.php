<?php
namespace Asgard\Files\Rules;

class Extension extends \Asgard\Validation\Rule {
	public $extension;

	public function __construct() {
		$this->extension = func_get_args();
	}

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		if(!$input instanceof \Asgard\Files\Libs\EntityFile || $input->get(null, true) === null)
			return;
		return in_array($input->extension(), $this->extension);
	}

	public function formatParameters(array &$params) {
		$params['extension'] = implode(', ', $params['extension']);
	}

	public function getMessage() {
		return 'The file :attribute must have one of the following extension: :extension.';
	}
}