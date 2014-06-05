<?php
namespace Asgard\Http\Tests;

class BrowserTest extends \PHPUnit_Framework_TestCase {
	protected static $app;

	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');

		$app = new \Asgard\Core\App;
		$app['cache'] = new \Asgard\Cache\NullCache;
		$app['resolver'] = new \Asgard\Http\Resolver($app);
		$app['httpkernel'] = new \Asgard\Http\HttpKernel($app);
		$app['hooks'] = new \Asgard\Hook\HooksManager($app);
		$app['resolver'] = new \Asgard\Http\Resolver($app['cache']);
		$app['resolver']->addRoute(new \Asgard\Http\ControllerRoute('', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$app->register('paginator', function($app, $args) {
			return new \Asgard\Utils\Paginator($args[0], $args[1], $args[2]);
		});
		static::$app = $app;
	}
	
	public function test1() {
		$browser = new \Asgard\Http\Browser\Browser(static::$app);
		$this->assertEquals('<h1>Asgard</h1><p>Hello!</p>', $browser->get('')->getContent());
	}
}