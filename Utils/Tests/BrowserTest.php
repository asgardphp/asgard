<?php
namespace Asgard\Utils\Tests;

class BrowserTest extends \PHPUnit_Framework_TestCase {
	protected static $app;

	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');

		$app = new \Asgard\Core\App;
		$app['hook'] = new \Asgard\Hook\Hook($app);
		$app['cache'] = new \Asgard\Cache\NullCache;
		$app['resolver'] = new \Asgard\Http\Resolver($app['cache']);
		$app['resolver']->addRoute(new \Asgard\Http\ControllerRoute('', 'Asgard\Utils\Tests\Fixtures\HomeController', 'home'));
		$app->register('paginator', function($app, $args) {
			return new \Asgard\Utils\Paginator($args[0], $args[1], $args[2]);
		});
		static::$app = $app;
	}
	
	public function test1() {
		$browser = new \Asgard\Utils\Browser(static::$app);
		$doc = new \Asgard\Xpath\Doc($browser->get('')->content);
		$this->assertEquals($doc->text('//h1'), 'Asgard');
	}
}