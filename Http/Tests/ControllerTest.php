<?php
namespace Asgard\Hook\Tests;

use \Asgard\Http\Controller;
use \Asgard\Http\ControllerRoute;
use \Asgard\Http\Resolver;
use \Asgard\Http\Request;

class ControllerTest extends \PHPUnit_Framework_TestCase {
	public function testAnnotationsAndRouteFor() {
		\Asgard\Container\Container::instance()['cache'] = new \Asgard\Cache\NullCache;
		$routes = \Asgard\Http\Tests\Fixtures\Controllers\FooController::fetchRoutes();
		$route = $routes[0];
		$this->assertEquals('page/:id', $route->getRoute());
		$this->assertEquals('example.com', $route->get('host'));
		$this->assertEquals(['src'=>['type'=>'regex', 'regex'=>'.+']], $route->get('requirements'));
		$this->assertEquals('get', $route->get('method'));
		$this->assertEquals('foo', $route->get('name'));

		$this->assertEquals($route, \Asgard\Http\Tests\Fixtures\Controllers\FooController::routeFor('page'));
	}

	public function testFilters() {
		$app = new \Asgard\Container\Container;
		$app['hooks'] = new \Asgard\Hook\HooksManager($app);
		$controller = new \Asgard\Http\Tests\Fixtures\Controllers\FooController();
		$controller->addFilter(new _Filter);
		$controller->run('page', $app);

		$this->assertEquals('bar', $controller->foo);
		$this->assertEquals('foo', $controller->bar);
	}

	public function testControllerRoute() {
		$resolver = new Resolver(new \Asgard\Cache\NullCache);
		$resolver->addRoute(new ControllerRoute('test', 'Asgard\Http\Tests\Fixtures\Controllers\FooController', 'page'));
		$request = new Request;
		$request->url->setURL('test');
		$callback = $resolver->getCallback($request);
		$arguments = $resolver->getArguments($request);

		$app = new \Asgard\Container\Container;
		$app['hooks'] = new \Asgard\Hook\HooksManager($app);
		$response = call_user_func_array($callback, array_merge($arguments, [$app, $request]));
		$this->assertEquals('hello!', $response->content);
	}
}

class _Filter extends \Asgard\Http\Filter {
	public function before($chain, $controller, $request) {
		$controller->foo = 'bar';
	}

	public function after($chain, $controller, &$result) {
		$controller->bar = 'foo';
	}
}