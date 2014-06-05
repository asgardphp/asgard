<?php
namespace Asgard\Core;

abstract class Facade {
	public static function __callStatic($name, array $args) {
		if(method_exists(static::inst(), $name))
			return call_user_func_array(array(static::inst(), $name), $args);
		else
			throw new \Exception('Cannot call '.static::getClass().'->'.$name);
	}

	public static function inst() {
		return App::instance()->get(strtolower(get_called_class()));
	}
}