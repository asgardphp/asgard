<?php
namespace Asgard\Utils;

class Timer {
	protected static $time;

	public static function start() {
		$t = static::get();
		static::$time = $t;
	}

	public static function end() {
		$t = static::get();
		return array($t-static::$time, static::$time, $t);
	}

	public static function get() {
		return time()+microtime();
	}
}