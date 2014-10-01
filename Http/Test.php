<?php
namespace Asgard\Http;

/**
 * Test parent class.
 */
abstract class Test extends \PHPUnit_Framework_TestCase {
	/**
	 * Services container.
	 * @var \Asgard\Container\ContainerInterface
	 */
	protected static $container;

	/**
	 * Get the container.
	 * @return \Asgard\Container\ContainerInterface
	 */
	protected static function getContainer() {
		if(!static::$container)
			static::$container = \Asgard\Container\Container::singleton();
		return static::$container;
	}

	/**
	 * Get a browser instance.
	 * @return Browser\Browser
	 */
	protected function createBrowser() {
		return static::getContainer()->make('browser');
	}
}