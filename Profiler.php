<?php
namespace Coxis\Utils;

class Profiler {
	public static $checkpoints = array();

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
if(defined('_START_')) {
	Profiler::$checkpoints[] = array(
		'name'	=>	'start',
		'time'	=>	_START_,
	);
}