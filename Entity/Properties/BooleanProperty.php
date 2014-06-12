<?php
namespace Asgard\Entity\Properties;

class BooleanProperty extends \Asgard\Entity\Property {
	public function getRules() {
		$rules = parent::getRules();
		$rules['required'] = false;
		return $rules;
	}

	public function getSQLType() {
		return 'int(1)';
	}

	public function _getDefault() {
		return false;
	}

	public function getFormField() {
		return 'Asgard\Form\Fields\BooleanField';
	}
}