<?php
namespace Asgard\Http\Inputs;

class Session extends InputsBag {
	public function set($name, $value=null) {
		if(is_array($name) && array_values($name) !== $name) {
			foreach($name as $k=>$v)
				static::set($k, $v);
		}
		else
			\Asgard\Utils\Tools::array_set($_SESSION, $name, $value);
		return parent::set($name, $value);
	}
	  
	public function remove($name) {
		\Asgard\Utils\Tools::array_unset($_SESSION, $name);
		return parent::remove($name);
	}
}