<?php
namespace Asgard\Orm\Rules;

/**
 * Verify that there is at least 1 entity.
 */
class Relationrequired extends \Asgard\Validation\Rule {
	/**
	 * {@inheritdoc}
	 */
	public function validate($input) {
		return $input->count() > 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMessage() {
		return ':attribute is required.';
	}
}