<?php
namespace Asgard\Entity\Properties;

class ArrayProperty extends BaseProperty {
	public function getRules() {
		$rules = parent::getRules();
		$rules['array'] = true;

		return $rules;
	}

	public function getSQLType() {
		return 'text';
	}

	protected function _getDefault() {
		return array();
	}

	public function serialize($obj) {
		return serialize($obj);
	}

	public function unserialize($str) {
		try {
			return unserialize($str);
		} catch(\ErrorException $e) {
			return array($str);
		}
	}

	public function set($val) {
		if(is_array($val))
			return $val;
		try {
			$res = unserialize($val);
			return $res;	
		} catch(\Exception $e) {
			return array($val);
		}
	}
}
