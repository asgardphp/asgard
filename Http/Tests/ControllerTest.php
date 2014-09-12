<?php
namespace Asgard\Http\Tests;

use \Asgard\Http\Controller;
use \Asgard\Http\ControllerRoute;
use \Asgard\Http\Resolver;
use \Asgard\Http\Route;
use \Asgard\Http\Request;

class ControllerTest extends \PHPUnit_Framework_TestCase {
	public function testAnnotationsAndRouteFor() {
		$annotationsReader = new \Asgard\Http\AnnotationsReader;
		$routes = $annotationsReader->fetchRoutes('Asgard\Http\Tests\Fixtures\Controllers\FooController');
		$route = $routes[0];
		$this->assertEquals('page/:id', $route->getRoute());
		$this->assertEquals('example.com', $route->get('host'));
		$this->assertEquals(['src'=>['type'=>'regex', 'regex'=>'.+']], $route->get('requirements'));
		$this->assertEquals('get', $route->get('method'));
		$this->assertEquals('foo', $route->get('name'));
	}

	public function testFilters() {
		$container = new \Asgard\Container\Container;
		$container['hooks'] = new \Asgard\Hook\HooksManager($container);
		$controller = new \Asgard\Http\Tests\Fixtures\Controllers\FooController();
		$controller->addFilter(new _Filter);
		$controller->run('page', new Request);

		$this->assertEquals('bar', $controller->foo);
		$this->assertEquals('foo', $controller->bar);
	}

	public function testControllerRoute() {
		$cache = new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache);
		$resolver = new Resolver($cache);
		$resolver->addRoute(new Route('test', 'Asgard\Http\Tests\Fixtures\Controllers\FooController', 'page'));
		$request = new Request;
		$request->url->setURL('test');
		$route = $resolver->getRoute($request);
		$controller = $route->getController();
		$action = $route->getAction();

		$container = new \Asgard\Container\Container;
		$container['hooks'] = new \Asgard\Hook\HooksManager($container);

		$controller = new $controller();
		$controller->setContainer($container);

		$response = $controller->run($action, $request);

		$this->assertEquals('hello!', $response->getContent());
	}
}

class _Filter extends \Asgard\Http\Filter {
	public function before(\Asgard\Http\Controller $controller, \Asgard\Http\Request $request) {
		$controller->foo = 'bar';
	}

	public function after(\Asgard\Http\Controller $controller, \Asgard\Http\Request $request, &$result) {
		$controller->bar = 'foo';
	}
}