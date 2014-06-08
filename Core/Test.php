<?php
namespace Asgard\Core;

abstract class Test extends \PHPUnit_Framework_TestCase {
	protected static $app;

	protected static function getApp() {
		if(!static::$app)
			static::$app = \Asgard\Core\App::instance();
		return static::$app;
	}

	protected function getBrowser() {
		return new \Asgard\Http\Browser\Browser(static::getApp());
	}
}