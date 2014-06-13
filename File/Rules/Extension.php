<?php
namespace Asgard\File\Rules;

class Extension extends \Asgard\Validation\Rule {
	public $extension;
	protected $handleEach = true;

	public function __construct() {
		$this->extension = func_get_args();
	}

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		return in_array($input->extension(), $this->extension);
	}

	public function formatParameters(array &$params) {
		$params['extension'] = implode(', ', $params['extension']);
	}

	public function getMessage() {
		return 'The file :attribute must have one of the following extension: :extension.';
	}
}