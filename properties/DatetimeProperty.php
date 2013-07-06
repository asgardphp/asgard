<?php
namespace Coxis\Core\Properties;

class DatetimeProperty extends BaseProperty {
	public function getRules() {
		$rules = parent::getRules();
		$rules['datetime'] = true;

		return $rules;
	}

	public function _getDefault() {
		return new \Coxis\Utils\Datetime;
	}

	public function serialize($obj) {
		if($obj == null)
			return '';
		return date('Y-m-d H:i:s', $obj->timestamp);
	}

	public function unserialize($str) {
		try {
			preg_match('/([0-9]+)-([0-9]+)-([0-9]+) ([0-9]+):([0-9]+):([0-9]+)/', $str, $r);
			$t = mktime($r[4], $r[5], $r[6], $r[2], $r[3], $r[1]);
			return new \Coxis\Utils\Datetime($t);
		}
		catch(\Exception $e) {
			return $this->_getDefault();
		}
	}

	public function set($val) {
		if(!$val)
			return null;
		$b = preg_match('/([0-9]+)\/([0-9]+)\/([0-9]+) ([0-9]+):([0-9]+):([0-9]+)/', $val, $r);
		if(!$b)
			return null;
		$t = mktime($r[4], $r[5], $r[6], $r[2], $r[1], $r[3]);
		return new \Coxis\Utils\Datetime($t);
	}

	public function getSQLType() {
		return 'datetime';
	}
}