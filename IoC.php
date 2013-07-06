<?php
namespace Coxis\Core;

class IoC {
	protected $registry = array();

	public function register($name, $callback) {
		$this->registry[$name] = $callback;
	}
	
	public function get($name) {
		$params = func_get_args();
		if(isset($params[0]))
			unset($params[0]);
		return call_user_func_array($this->registry[$name], $params);
	}
	
	public function set($name, $object) {
		$this->registry[$name] = $object;
	}

	public function registered($name) {
		return isset($this->registry[$name]);
	}
}