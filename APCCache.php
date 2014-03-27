<?php
namespace Asgard\Cache;

class APCCache implements CacheInterface {
	protected $path;
	protected $active;

	public function __construct($path=null, $active=true) {
		$this->path = $path;
		$this->active = $active;
	}

	public function clear() {
		if(!$this->active)
			return;

		foreach(\apc_fetch($this->path.'/__identifiers') as $identifier)
			apc_delete($this->path.'/'.$identifier);
	}

	public function get($identifier, $default=null) {
		if(!$this->active)
			return;

		$success = null;
		try {
			$res = \apc_fetch($this->path.'/'.$identifier, $success);
		} catch(\Exception $e) { $success = false; }
		if($success)
			return $res;

		if(\Asgard\Utils\Tools::is_function($default)) {
			$r = $default();
			static::set($identifier, $r);
			return $r;
		}
		else
			return $default;
	}
	
	public function set($identifier, $var) {
		if(!$this->active)
			return false;

		try {
			$r = apc_store($this->path.'/'.$identifier, $var);
		} catch(\Exception $e) { return false; }
		if(!$r)
			return false;

		$identifiers = \apc_fetch($this->path.'/__identifiers');
		$identifiers[$identifier] = true;
		apc_store($this->path.'/__identifiers', $identifiers);

		return true;
	}
	
	public function delete($identifier) {
		if(!$this->active)
			return;

		$r = apc_delete($this->path.'/'.$identifier);
		if($r) {
			$identifiers = \apc_fetch($this->path.'/__identifiers');
			unset($identifiers[$identifier]);
			apc_store($this->path.'/__identifiers', $identifiers);
		}
		return $r;
	}
}