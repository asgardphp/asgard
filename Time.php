<?php
namespace Coxis\Utils;

class Time {
	public $timestamp = 0;

	public function __construct($t=null) {
		if($t === null)
			$t = time();
		$this->timestamp = $t;
	}

	public static function isNull() {
		return $this->timestamp == 0;
	}

	public function iso() {
		return date('r', $this->timestamp);
	}
	
	public function datetime() {
		return $this->format('Y-d-m H:i:s');
	}
	
	public function date() {
		return $this->format('d/m/Y');
	}

	public function format($format) {
		$format = preg_replace('/(?<!\\\\)M/', Tools::dateEscape(Tools::$shortMonths[date('M', $this->timestamp)]), $format); #Jan
		$format = preg_replace('/(?<!\\\\)F/', Tools::dateEscape(Tools::$months[date('F', $this->timestamp)]), $format); #January
		$format = preg_replace('/(?<!\\\\)D/', Tools::dateEscape(Tools::$shortDays[date('D', $this->timestamp)]), $format); #Mon
		$format = preg_replace('/(?<!\\\\)l/', Tools::dateEscape(Tools::$days[date('l', $this->timestamp)]), $format); #Monday
		return date($format, $this->timestamp);
	}

	public static function dateToSQLFormat($date) {
		if($date=='')
			return '';
		list($d, $m, $y) = explode('/', $date);
		return $y.'-'.$m.'-'.$d;
	}
	
	public static function SQLFormatToDate($date) {
		if($date=='')
			return '';
		list($y, $m, $d) = explode('-', $date);
		return $d.'/'.$m.'/'.$y;
	}
	
	public static function toTimestamp($value) {
		try {
			list($d, $m, $y) = explode('/', $value);
			return mktime(0, 0, 0, $m, $d, $y);
		} catch(Exception $e) {
			return 0;
		}
	}
	
	public static function toDate($value) {
		return date('d/m/Y', $value);
	}
}