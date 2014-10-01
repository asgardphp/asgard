<?php
namespace Asgard\Validation\Rules;

/**
 * Check that the input is an email address.
 */
class Email extends \Asgard\Validation\Rule {
	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		return filter_var($input, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must be a valid email address.';
	}
}