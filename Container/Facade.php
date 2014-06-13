<?php
namespace Asgard\Container;

abstract class Facade {
	public static function __callStatic($name, array $args) {
		if(method_exists(static::inst(), $name))
			return call_user_func_array([static::inst(), $name], $args);
		else
			throw new \Exception('Cannot call '.get_called_class().'->'.$name);
	}

	public static function inst() {
		return Container::instance()->get(strtolower(get_called_class()));
	}
}