<?php
namespace Asgard\Orm\Rules;

/**
 * Verify that there is at least 1 entity.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Ormrequired extends \Asgard\Validation\Rule {
	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		return $input->count() > 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute is required.';
	}
}