<?php
namespace Asgard\Entity\Properties;

class ArrayProperty extends \Asgard\Entity\Property {
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

	protected function doSerialize($obj) {
		return serialize($obj);
	}

	protected function doUnserialize($str) {
		try {
			return unserialize($str);
		} catch(\ErrorException $e) {
			return array($str);
		}
	}

	protected function doSet($val) {
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
