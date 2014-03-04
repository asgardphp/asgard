<?php
namespace Asgard\Utils;

class Profiler {
	protected static $checkpoints = array();

	public static function checkpoint($name) {
		$bt = debug_backtrace();
		$cp = array(
			'name'	=>	$name,
			'time'	=>	Timer::get(),
		);
		if(isset($bt[1]))
			$cp['bt'] = $bt[1];
		static::$checkpoints[] = $cp;
	}

	public static function report() {
		if(defined('_COXIS_START_')) {
			array_unshift(array(
				'name'	=>	'start',
				'time'	=>	_COXIS_START_,
			));
		}
		static::$checkpoints[] = array(
			'name'	=>	'end',
			'time'	=>	Timer::get(),
		);
		$str = '';
		$init = static::$checkpoints[0]['time'];
		$prev = $init;
		foreach(static::$checkpoints as $cp) {
			$str .= number_format(($cp['time']-$prev)*1000, 0).'ms/'.number_format(($cp['time']-$init)*1000, 0).'ms - '.$cp['name']."\n";
			if(isset($cp['bt']['class']))
				$str .= 'Function: '.$cp['bt']['class'].'/'.$cp['bt']['function']."\n";
			if(isset($cp['bt']['file']))
				$str .= 'File: '.$cp['bt']['file'].' ('.$cp['bt']['line'].')'."\n";
			$str .= "\n";
			$prev = $cp['time'];
		}
		Log::add('profiler/'.date('Y-m-d H-i-s').'.txt', $str);
	}
}