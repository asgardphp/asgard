<?php
namespace Coxis\Core;

class IoC {
	protected $registry = array();

	public function register($name, $callback) {
		$this->registry[$name] = $callback;
	}
	
	public function get($name, $params=array(), $default=null) {
		if(isset($this->registry[$name]))
			return call_user_func_array($this->registry[$name], $params);
		else {
			if($default instanceof \Closure)
				return call_user_func_array($default, $params);
			else
				return $default;
		}
	}

	public function registered($name) {
		return isset($this->registry[$name]);
	}
}