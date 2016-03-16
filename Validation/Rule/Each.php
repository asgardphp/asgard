<?php
namespace Asgard\Validation\Rule;

/**
 * Check that each element of the iterable input validates the given validator.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Each extends \Asgard\Validation\Rule {
	/**
	 * Validator.
	 * @var \Asgard\Validation\ValidatorInterface
	 */
	public $validator;

	/**
	 * Constructor.
	 * @param \Asgard\Validation\ValidatorInterface $validator
	 */
	public function __construct(\Asgard\Validation\ValidatorInterface $validator) {
		$this->validator = $validator;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		foreach($input as $k=>$v) {
			if($this->validator->valid($v) === false)
				return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute is invalid.';
	}
}