<?php
namespace Asgard\File\Rules;

/**
 * Check that the file's extension is allowed.
 */
class Extension extends \Asgard\Validation\Rule {
	public $extension;
	protected $handleEach = true;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->extension = func_get_args();
	}

	/**
	 * {@inherits}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		return in_array($input->extension(), $this->extension);
	}

	/**
	 * {@inherits}
	 */
	public function formatParameters(array &$params) {
		$params['extension'] = implode(', ', $params['extension']);
	}

	/**
	 * {@inherits}
	 */
	public function getMessage() {
		return 'The file :attribute must have one of the following extension: :extension.';
	}
}