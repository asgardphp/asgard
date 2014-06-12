<?php
namespace Asgard\Hook\Tests;

use \Asgard\Http\Request;
use \Asgard\Http\Resolver;
use \Asgard\Http\Route;

class ResolverTest extends \PHPUnit_Framework_TestCase {
	public function testGetRoute() {
		$cache = new \Asgard\Cache\NullCache;
		$resolver = new Resolver($cache);
		$route = new Route('test/:id/plpl', 'callback', [1,2,3]);

		$resolver->addRoute($route);

		$request = new Request;
		$request->url->setURL('test/plpl');
		$this->assertEquals(null, $resolver->getCallback($request));
		$request->url->setURL('test/1/plpl');
		$this->assertEquals('callback', $resolver->getCallback($request));
		$this->assertEquals([1,2,3], $resolver->getArguments($request));
	}

	public function testSortRoutes() {
		$cache = new \Asgard\Cache\NullCache;
		$resolver = new Resolver($cache);

		$resolver->addRoute(new Route('test', null));
		$resolver->addRoute(new Route(':a', null));
		$resolver->addRoute(new Route('test/abc', null));
		$resolver->addRoute(new Route('test/:id/plpl', null));

		$routes = $resolver->sortRoutes()->getRoutes();

		$res = [];
		foreach($routes as $r)
			$res[] = $r->getRoute();
		$this->assertEquals([
			'test/abc', 'test/:id/plpl', 'test', ':a'
		], $res);
	}

	public function testParameter() {
		$cache = new \Asgard\Cache\NullCache;
		$resolver = new Resolver($cache);
		$route = new Route('test/:id/plpl', 'callback', [1,2,3]);

		$resolver->addRoute($route);

		$request = new Request;
		$request->url->setURL('test/1/plpl');
		$resolver->getCallback($request);
		$this->assertEquals('1', $request->getParam('id'));
	}

	public function testBuildRoute() {
		$cache = new \Asgard\Cache\NullCache;
		$resolver = new Resolver($cache);
		$this->assertEquals('test/1/plpl', $resolver->buildRoute('test/:id/plpl', ['id'=>1]));
	}
}
