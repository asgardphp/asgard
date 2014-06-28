<?php
namespace Asgard\Http;

abstract class Test extends \PHPUnit_Framework_TestCase {
	protected static $app;

	protected static function getApp() {
		if(!static::$app)
			static::$app = \Asgard\Container\Container::singleton();
		return static::$app;
	}

	protected function getBrowser() {
		return new \Asgard\Http\Browser\Browser(static::getApp());
	}
}