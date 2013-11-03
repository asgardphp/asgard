<?php
namespace Coxis\Core;

abstract class Facade {
	public static function __callStatic($name, $args) {
		if(method_exists(static::inst(), $name))
			return call_user_func_array(array(static::inst(), $name), $args);
		else
			throw new \Exception('Cannot call '.static::getClass().'->'.$name);
	}

	public static function inst() {
		return Context::get(static::getClass());
	}

	public static function getClass() {
		if(isset(static::$class))
			return static::$class;
		else
			return strtolower(NamespaceUtils::basename(get_called_class()));
	}
}