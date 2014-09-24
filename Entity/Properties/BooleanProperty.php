<?php
namespace Asgard\Entity\Properties;

/**
 * Boolean Property.
 */
class BooleanProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function getRules() {
		$rules = parent::getRules();
		$rules['required'] = false;
		return $rules;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSQLType() {
		return 'int(1)';
	}

	/**
	 * {@inheritDoc}
	 */
	public function _getDefault() {
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFormField() {
		return 'Asgard\Form\Fields\BooleanField';
	}
}