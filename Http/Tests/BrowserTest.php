<?php
namespace Asgard\Http\Tests;

class BrowserTest extends \PHPUnit_Framework_TestCase {
	protected static $container;

	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');

		$container = new \Asgard\Container\Container;
		$container['cache'] = new \Asgard\Cache\NullCache;
		$container['resolver'] = new \Asgard\Http\Resolver($container['cache']);
		$container['httpkernel'] = new \Asgard\Http\HttpKernel($container);
		$container['hooks'] = new \Asgard\Hook\HooksManager($container);
		$container['resolver'] = new \Asgard\Http\Resolver($container['cache']);
		$container['resolver']->addRoute(new \Asgard\Http\Route('', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$container->register('paginator', function($container, $args) {
			return new \Asgard\Common\Paginator($args[0], $args[1], $args[2]);
		});
		static::$container = $container;
	}
	
	public function test1() {
		$browser = new \Asgard\Http\Browser\Browser(static::$container);
		$this->assertEquals('<h1>Asgard</h1><p>Hello!</p>', $browser->get('')->getContent());
	}
}