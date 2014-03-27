<?php
namespace Asgard\Files\Rules;

class Extension extends \Asgard\Validation\Rule {
	public $extension;

	public function __construct() {
		$this->extension = func_get_args();
	}

	public function validate($input, $parentInput, $validator) {
		return in_array($input->extension(), $this->extension);
	}

	public function formatParameters(&$params) {
		$params['extension'] = implode(', ', $params['extension']);
	}

	public function getMessage() {
		return 'The file :attribute must have one of the following extension: :extension.';
	}
}