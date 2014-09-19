<?php
namespace Asgard\Entity\Properties;

/**
 * Boolean Property.
 */
class BooleanProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritdoc}
	 */
	public function getRules() {
		$rules = parent::getRules();
		$rules['required'] = false;
		return $rules;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSQLType() {
		return 'int(1)';
	}

	/**
	 * {@inheritdoc}
	 */
	public function _getDefault() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormField() {
		return 'Asgard\Form\Fields\BooleanField';
	}
}