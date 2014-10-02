<?php
namespace Asgard\Validation\Rules;

/**
 * Check that the input is an integer.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Int extends \Asgard\Validation\Rule {
	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		return is_int($input);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must be an integer.';
	}
}