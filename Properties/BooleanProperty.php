<?php
namespace Asgard\Core\Properties;

class BooleanProperty extends BaseProperty {
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
}