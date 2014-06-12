<?php
namespace Asgard\Common;

class ArrayUtils {
	public static function string_array_get($arr, $str_path, $default=null) {
		$path = explode('.', $str_path);
		return static::array_get($arr, $path, $default);
	}

	public static function string_array_set(&$arr, $str_path, $value) {
		$path = explode('.', $str_path);
		static::array_set($arr, $path, $value);
	}

	public static function string_array_isset($arr, $str_path) {
		$path = explode('.', $str_path);
		return static::array_isset($arr, $path);
	}

	public static function string_array_unset(&$arr, $str_path) {
		$path = explode('.', $str_path);
		static::array_unset($arr, $path);
	}

	public static function array_set(&$arr, $path, $value) {
		if(!is_array($path))
			$path = [$path];
		$lastkey = array_pop($path);
		foreach($path as $parent)
			$arr =& $arr[$parent];
		$arr[$lastkey] = $value;
	}
	
	public static function array_get($arr, $path, $default=null) {
		if(!is_array($path))
			$path = [$path];
		foreach($path as $key) {
			if(!isset($arr[$key]))
				return $default;
			else
				$arr = $arr[$key];
		}
		return $arr;
	}
	
	public static function array_isset($arr, $keys) {
		if(!$keys)
			return;
		if(!is_array($keys))
			$keys = [$keys];
		foreach($keys as $key) {
			if(!isset($arr[$key]))
				return false;
			else
				$arr = $arr[$key];
		}
		return true;
	}
	
	public static function array_unset(&$arr, $keys) {
		if(!$keys)
			return;
		if(!is_array($keys))
			$keys = [$keys];
		$lastkey = array_pop($keys);
		foreach($keys as $parent)
			$arr =& $arr[$parent];
		unset($arr[$lastkey]);
	}

	public static function flateArray($arr) {
		if(!is_array($arr))
			return [$arr];
		$res = [];
		foreach($arr as $k=>$v) {
			if(is_array($v))
				$res = array_merge($res, static::flateArray($v));
			else
				$res[] = $v;
		}
				
		return $res;
	}
	
	public static function array_before($arr, $i) {
		$res = [];
		foreach($arr as $k=>$v) {
			if($k === $i)
				return $res;
			$res[$k] = $v;
		}
		return $res;
	}

	public static function array_after($arr, $i) {
		$res = [];
		$do = false;
		foreach($arr as $k=>$v) {
			if($do)
				$res[$k] = $v;
			if($k === $i)
				$do = true;
		}
		return $res;
	}
}