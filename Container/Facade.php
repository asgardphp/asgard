<?php
namespace Asgard\Container;

/**
 * Parent for all facades.
 * @author Michel Hognerud <michel@hognerud.com>
 */
abstract class Facade {
	/**
	 * __callStatic magic method.
	 * Forward all static calls to the instance.
	 * @param  string $name
	 * @param  array  $args
	 * @return mixed
	 */
	public static function __callStatic($name, array $args) {
		if(method_exists(static::inst(), $name))
			return call_user_func_array([static::inst(), $name], $args);
		else
			throw new \Exception('Cannot call '.get_called_class().'->'.$name);
	}

	/**
	 * Return the instance.
	 * @return mixed
	 */
	public static function inst() {
		return Container::singleton()->get(strtolower(get_called_class()));
	}
}