<?php
namespace Asgard\Entity\Properties;

/**
 * Boolean Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class BooleanProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function prepareValidator(\Asgard\Validation\ValidatorInterface $validator) {
		parent::prepareValidator($validator);
		$validator->rule('required', false);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getORMParameters() {
		return [
			'type' => 'integer',
			'length' => 1,
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function _getDefault() {
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFormField() {
		return 'Asgard\Form\Fields\BooleanField';
	}
}