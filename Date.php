<?php
namespace Asgard\Utils;

class Date extends Time {
	public function __toString() {
		return $this->format('d/m/Y');
	}

	public static function isNull() {
		return $this->timestamp == 0;
	}
	
	public static function fromDate($v) {
		if(!$v)
			return 0;
		if($v instanceof Time)
			return $v;
		list($d, $m, $y) = explode('/', $v);
		$timestamp = mktime(0, 0, 0, $m, $d, $y);
		return new static($timestamp);
	}
}