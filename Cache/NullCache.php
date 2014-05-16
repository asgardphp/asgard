<?php
namespace Asgard\Cache;

class NullCache implements CacheInterface {
	public function __construct($path=null) {
		$this->path = $path;
	}

	public function clear() {}

	public function get($identifier, $default=null) {
		if(is_callable($default)) {
			$r = $default();
			return $r;
		}
		else
			return $default;
	}
	
	public function set($file, $var) {
		return false;
	}
	
	public function delete($file) {}
}