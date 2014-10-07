<?php
namespace Asgard\Validation\Rules;

/**
 * Check that the input has a date format.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Date extends \Asgard\Validation\Rule {
	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		if($input instanceof \Asgard\Common\DatetimeInterface)
			return true;
		if(is_string($input))
			return preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $input) === 1;
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must be a date (yyyy-mm-dd).';
	}
}