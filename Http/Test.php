<?php
namespace Asgard\Http;

abstract class Test extends \PHPUnit_Framework_TestCase {
	protected static $container;

	protected static function getContainer() {
		if(!static::$container)
			static::$container = \Asgard\Container\Container::singleton();
		return static::$container;
	}

	protected function getBrowser() {
		return new \Asgard\Http\Browser\Browser(static::getContainer());
	}
}