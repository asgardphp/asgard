<?php
namespace Asgard\Cache;

class APCCache implements CacheInterface {
	protected $path;

	public function __construct($path=null) {
		$this->path = $path;
	}

	public function clear() {
		foreach(\apc_fetch($this->path.'/__identifiers') as $identifier)
			apc_delete($this->path.'/'.$identifier);
	}

	public function get($identifier, $default=null) {
		$success = null;
		try {
			$res = \apc_fetch($this->path.'/'.$identifier, $success);
		} catch(\Exception $e) { $success = false; }
		if($success)
			return $res;

		if(is_callable($default)) {
			$r = $default();
			static::set($identifier, $r);
			return $r;
		}
		else
			return $default;
	}
	
	public function set($identifier, $var) {
		try {
			if(!($r = apc_store($this->path.'/'.$identifier, $var)))
				return false;
		} catch(\Exception $e) {
			return false;
		}

		$identifiers = \apc_fetch($this->path.'/__identifiers');
		$identifiers[$identifier] = true;
		apc_store($this->path.'/__identifiers', $identifiers);

		return true;
	}
	
	public function delete($identifier) {
		$r = apc_delete($this->path.'/'.$identifier);
		if($r) {
			$identifiers = \apc_fetch($this->path.'/__identifiers');
			unset($identifiers[$identifier]);
			apc_store($this->path.'/__identifiers', $identifiers);
		}
		return $r;
	}
}