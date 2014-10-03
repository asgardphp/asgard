<?php
namespace Asgard\Http\Tests;

use \Asgard\Http\Controller;
use \Asgard\Http\ControllerRoute;
use \Asgard\Http\Resolver;
use \Asgard\Http\Request;
use \Asgard\Http\HttpKernel;
use \Asgard\Http\Route;

class FiltersTest extends \PHPUnit_Framework_TestCase {
	public function testFilterAll() {
		$cache = new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache);
		$resolver = new \Asgard\Http\Resolver($cache);
		$httpKernel = new HttpKernel;
		$httpKernel->setResolver($resolver);
		$httpKernel->setHooksManager(new \Asgard\Hook\HooksManager);

		$httpKernel->filterAll('Asgard\Http\Tests\Fixtures\Filter');
		$resolver->addRoute(new Route('home', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$request = new \Asgard\Http\Request();
		$request->url->setURL('home');
		$this->assertEquals('foo!', $httpKernel->process($request)->getContent());
	}

	public function testFilter() {
		$cache = new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache);
		$resolver = new \Asgard\Http\Resolver($cache);
		$httpKernel = new HttpKernel;
		$httpKernel->setResolver($resolver);
		$httpKernel->setHooksManager(new \Asgard\Hook\HooksManager);

		$httpKernel->filter(['route'=>'home'], 'Asgard\Http\Tests\Fixtures\Filter');
		$resolver->addRoute(new Route('home', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$request = new \Asgard\Http\Request();
		$request->url->setURL('home');
		$this->assertEquals('foo!', $httpKernel->process($request, false)->getContent());
	}

	public function testBeforeAll() {
		$cache = new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache);
		$resolver = new \Asgard\Http\Resolver($cache);
		$httpKernel = new HttpKernel;
		$httpKernel->setResolver($resolver);
		$httpKernel->setHooksManager(new \Asgard\Hook\HooksManager);

		$httpKernel->filterBeforeAll(function(){return 'foo!';});
		$resolver->addRoute(new Route('home', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$request = new \Asgard\Http\Request();
		$request->url->setURL('home');
		$this->assertEquals('foo!', $httpKernel->process($request, false)->getContent());
	}

	public function testBefore() {
		$cache = new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache);
		$resolver = new \Asgard\Http\Resolver($cache);
		$httpKernel = new HttpKernel;
		$httpKernel->setResolver($resolver);
		$httpKernel->setHooksManager(new \Asgard\Hook\HooksManager);

		$httpKernel->filterBefore(['Asgard\Http\Tests\Fixtures\HomeController::home'], function(){return 'foo!';});
		$resolver->addRoute(new Route('home', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$request = new \Asgard\Http\Request();
		$request->url->setURL('home');
		$this->assertEquals('foo!', $httpKernel->process($request, false)->getContent());
	}

	public function testAfterAll() {
		$cache = new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache);
		$resolver = new \Asgard\Http\Resolver($cache);
		$httpKernel = new HttpKernel;
		$httpKernel->setResolver($resolver);
		$httpKernel->setHooksManager(new \Asgard\Hook\HooksManager);

		$httpKernel->filterAfterAll(function($controller, $request, &$result){$result = 'foo!';});
		$resolver->addRoute(new Route('home', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$request = new \Asgard\Http\Request();
		$request->url->setURL('home');
		$this->assertEquals('foo!', $httpKernel->process($request, false)->getContent());
	}

	public function testAfter() {
		$cache = new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache);
		$resolver = new \Asgard\Http\Resolver($cache);
		$httpKernel = new HttpKernel;
		$httpKernel->setResolver($resolver);
		$httpKernel->setHooksManager(new \Asgard\Hook\HooksManager);

		$httpKernel->filterAfter(['actions'=>'Asgard\Http\Tests\Fixtures'], function($controller, $request, &$result){$result = 'foo!';});
		$resolver->addRoute(new Route('home', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$request = new \Asgard\Http\Request();
		$request->url->setURL('home');
		$this->assertEquals('foo!', $httpKernel->process($request, false)->getContent());
	}

	public function testLayout() {
		$controller = new \Asgard\Http\Tests\Fixtures\Controllers\FooController();
		$controller->addFilter(new \Asgard\Http\Filters\PageLayout(function($content) { return '<h1>'.$content.'</h1>'; }));
		$res = $controller->run('page', new Request);

		$this->assertEquals('<h1>hello!</h1>', $res->getContent());
	}
}