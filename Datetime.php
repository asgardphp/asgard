<?php
namespace Asgard\Utils;

class Datetime extends Time {
	public function __toString() {
		return $this->format('d/m/Y H:i:s');
	}
	
	public static function fromDatetime($v) {
		if(!$v)
			return 0;
		if($v instanceof Time)
			return $v;
		preg_match('/([0-9]+)\/([0-9]+)\/([0-9]+) ([0-9]+):([0-9]+):([0-9]+)/', $v, $r);
		d($r);
		return new static($timestamp);
	}
}