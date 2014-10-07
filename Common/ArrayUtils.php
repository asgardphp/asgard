<?php
namespace Asgard\Common;

/**
 * Array utils.
 * @author Michel Hognerud <michel@hognerud.com>
 * @api
 */
class ArrayUtils {
	/**
	 * Get element from array, with a path.
	 * @param  array  $arr
	 * @param  string $str_path  nested keys separated by ".".
	 * @param  mixed  $default
	 * @return mixed
	 * @api
	 */
	public static function get($arr, $str_path, $default=null) {
		$path = explode('.', $str_path);
		return static::array_get($arr, $path, $default);
	}

	/**
	 * Set element in array, with a path.
	 * @param  array  $arr
	 * @param  string $str_path  nested keys separated by ".".
	 * @param  mixed $value
	 * @api
	 */
	public static function set(&$arr, $str_path, $value) {
		$path = explode('.', $str_path);
		static::array_set($arr, $path, $value);
	}

	/**
	 * Check if element in array exists.
	 * @param  array  $arr
	 * @param  string $str_path  nested keys separated by ".".
	 * @return boolean
	 * @api
	 */
	public static function _isset($arr, $str_path) {
		$path = explode('.', $str_path);
		return static::array_isset($arr, $path);
	}

	/**
	 * Unset element in array.
	 * @param  array  $arr
	 * @param  string $str_path  nested keys separated by ".".
	 * @api
	 */
	public static function _unset(&$arr, $str_path) {
		$path = explode('.', $str_path);
		static::array_unset($arr, $path);
	}

	/**
	 * Set element in array.
	 * @param  array        $arr
	 * @param  string|array $path  list of nested keys.
	 * @param  mixed        $value
	 * @api
	 */
	public static function array_set(&$arr, $path, $value) {
		if(!is_array($path))
			$path = [$path];
		$lastkey = array_pop($path);
		foreach($path as $parent)
			$arr =& $arr[$parent];
		$arr[$lastkey] = $value;
	}

	/**
	 * Get element in array.
	 * @param  array        $arr
	 * @param  string|array $path  list of nested keys.
	 * @param  mixed        $default
	 * @return mixed
	 * @api
	 */
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

	/**
	 * Check if element exists in array.
	 * @param  array        $arr
	 * @param  string|array $path  list of nested keys.
	 * @return boolean
	 * @api
	 */
	public static function array_isset($arr, $path) {
		if(!$path)
			return;
		if(!is_array($path))
			$path = [$path];
		foreach($path as $key) {
			if(!isset($arr[$key]))
				return false;
			else
				$arr = $arr[$key];
		}
		return true;
	}

	/**
	 * Unset element in array.
	 * @param  array        $arr
	 * @param  string|array $path  list of nested keys.
	 * @api
	 */
	public static function array_unset(&$arr, $path) {
		if(!$path)
			return;
		if(!is_array($path))
			$path = [$path];
		$lastkey = array_pop($path);
		foreach($path as $parent)
			$arr =& $arr[$parent];
		unset($arr[$lastkey]);
	}

	/**
	 * Flatten an array.
	 * @param  array $arr
	 * @return array
	 * @api
	 */
	public static function flatten($arr) {
		if(!is_array($arr))
			return [$arr];
		$res = [];
		foreach($arr as $k=>$v) {
			if(is_array($v))
				$res = array_merge($res, static::flatten($v));
			else
				$res[] = $v;
		}
		return $res;
	}

	/**
	 * Return all ements before a given position.
	 * @param  array   $arr
	 * @param  integer $i
	 * @return array
	 * @api
	 */
	public static function before($arr, $i) {
		$res = [];
		foreach($arr as $k=>$v) {
			if($k === $i)
				return $res;
			$res[$k] = $v;
		}
		return $res;
	}

	/**
	 * Return all ements after a given position.
	 * @param  array   $arr
	 * @param  integer $i
	 * @return array
	 * @api
	 */
	public static function after($arr, $i) {
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