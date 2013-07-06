<?php
namespace Coxis\Core\Properties;

class DateProperty extends BaseProperty {
	public function getRules() {
		$rules = parent::getRules();
		$rules['date'] = true;

		return $rules;
	}

	public function _getDefault() {
		return new \Coxis\Utils\Date(0);
	}

	public function serialize($obj) {
		if($obj == null)
			return '';
		if(!is_object($obj))
			return '';
		$d = $obj->date();
		list($d, $m, $y) = explode('/', $d);
		return $y.'-'.$m.'-'.$d;
	}

	public function unserialize($str) {
		if($str == null)
			$str = '1970-01-01';
		list($y, $m, $d) = explode('-', $str);
		$str = $d.'/'.$m.'/'.$y;
		return \Coxis\Utils\Date::fromDate($str);
	}

	public function set($val) {
		if(!$val)
			return null;
		if(preg_match('/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/', $val))
			return \Coxis\Utils\Date::fromDate($val);
		else
			return $val;
	}

	public function getSQLType() {
		return 'date';
	}
}