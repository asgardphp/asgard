<?php
namespace Asgard\Hook\Tests;

use \Asgard\Http\Request;
use \Asgard\Http\Resolver;
use \Asgard\Http\Route;

class ResolverTest extends \PHPUnit_Framework_TestCase {
	public function testGetRoute() {
		$cache = new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache);
		$resolver = new Resolver($cache);
		$route = new Route('test/:id/plpl', 'controller', 'action');

		$resolver->addRoute($route);

		$request = new Request;
		$request->url->setURL('test/plpl');
		$this->assertEquals(null, $resolver->getRoute($request));
		$request->url->setURL('test/1/plpl');
		$this->assertEquals('controller', $resolver->getRoute($request)->getController());
		$this->assertEquals('action', $resolver->getRoute($request)->getAction());
	}

	public function testSortRoutes() {
		$cache = new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache);
		$resolver = new Resolver($cache);

		$resolver->addRoute(new Route('test', '', ''));
		$resolver->addRoute(new Route(':a', '', ''));
		$resolver->addRoute(new Route('test/abc', '', ''));
		$resolver->addRoute(new Route('test/:id/plpl', '', ''));

		$routes = $resolver->sortRoutes()->getRoutes();

		$res = [];
		foreach($routes as $r)
			$res[] = $r->getRoute();
		$this->assertEquals([
			'test/abc', 'test/:id/plpl', 'test', ':a'
		], $res);
	}

	public function testParameter() {
		$cache = new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache);
		$resolver = new Resolver($cache);
		$route = new Route('test/:id/plpl', 'callback', 'foo', [1,2,3]);

		$resolver->addRoute($route);

		$request = new Request;
		$request->url->setURL('test/1/plpl');
		$resolver->getRoute($request);
		$this->assertEquals('1', $request->getParam('id'));
	}

	public function testBuildRoute() {
		$cache = new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache);
		$resolver = new Resolver($cache);
		$this->assertEquals('test/1/plpl', $resolver->buildRoute('test/:id/plpl', ['id'=>1]));
	}
}
