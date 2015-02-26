<?php
namespace Asgard\Entity\Properties;

/**
 * Email Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class EmailProperty extends TextProperty {
	/**
	 * {@inheritDoc}
	 */
	public function getORMParameters() {
		return [
			'type' => 'string',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepareValidator(\Asgard\Validation\ValidatorInterface $validator) {
		parent::prepareValidator($validator);
		$validator->rule('email');
	}
}