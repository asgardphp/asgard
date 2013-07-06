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
	
	protected static function escape($str) {
		return '\\'.implode('\\', str_split($str));
	}

	#todo put i18n somewhere else, SoC
	public function format($format) {
		// d(static::escape('aaaa'));
		$format = str_replace('F', static::escape(Tools::$months[date('F', $this->timestamp)]), $format);
		// d($format);

	//~ try {
		return date($format, $this->timestamp);
		//~ }catch(Exception $e){d($this->timestamp, $e);}
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