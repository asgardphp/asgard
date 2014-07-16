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
		$cache = new \Asgard\Cache\Cache;
		$resolver = new \Asgard\Http\Resolver($cache);
		$app = new \Asgard\Container\Container([
			'errorHandler' => new \Asgard\Debug\ErrorHandler,
			'hooks' => new \Asgard\Hook\HooksManager,
			'resolver' => $resolver,
		]);

		$httpKernel = new HttpKernel($app);
		$httpKernel->filterAll('Asgard\Http\Tests\Fixtures\Filter');
		$resolver->addRoute(new Route('home', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$request = new \Asgard\Http\Request();
		$request->url->url = 'home';
		$this->assertEquals('foo!', $httpKernel->process($request)->getContent());
	}

	public function testFilter() {
		$cache = new \Asgard\Cache\Cache;
		$resolver = new \Asgard\Http\Resolver($cache);
		$app = new \Asgard\Container\Container([
			'errorHandler' => new \Asgard\Debug\ErrorHandler,
			'hooks' => new \Asgard\Hook\HooksManager,
			'resolver' => $resolver,
		]);

		$httpKernel = new HttpKernel($app);
		$httpKernel->filter(['route'=>'home'], 'Asgard\Http\Tests\Fixtures\Filter');
		$resolver->addRoute(new Route('home', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$request = new \Asgard\Http\Request();
		$request->url->url = 'home';
		$this->assertEquals('foo!', $httpKernel->process($request, false)->getContent());
	}

	public function testBeforeAll() {
		$cache = new \Asgard\Cache\Cache;
		$resolver = new \Asgard\Http\Resolver($cache);
		$app = new \Asgard\Container\Container([
			'errorHandler' => new \Asgard\Debug\ErrorHandler,
			'hooks' => new \Asgard\Hook\HooksManager,
			'resolver' => $resolver,
		]);

		$httpKernel = new HttpKernel($app);
		$httpKernel->filterBeforeAll(function(){return 'foo!';});
		$resolver->addRoute(new Route('home', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$request = new \Asgard\Http\Request();
		$request->url->url = 'home';
		$this->assertEquals('foo!', $httpKernel->process($request, false)->getContent());
	}

	public function testBefore() {
		$cache = new \Asgard\Cache\Cache;
		$resolver = new \Asgard\Http\Resolver($cache);
		$app = new \Asgard\Container\Container([
			'errorHandler' => new \Asgard\Debug\ErrorHandler,
			'hooks' => new \Asgard\Hook\HooksManager,
			'resolver' => $resolver,
		]);

		$httpKernel = new HttpKernel($app);
		$httpKernel->filterBefore(['Asgard\Http\Tests\Fixtures\HomeController::home'], function(){return 'foo!';});
		$resolver->addRoute(new Route('home', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$request = new \Asgard\Http\Request();
		$request->url->url = 'home';
		$this->assertEquals('foo!', $httpKernel->process($request, false)->getContent());
	}

	public function testAfterAll() {
		$cache = new \Asgard\Cache\Cache;
		$resolver = new \Asgard\Http\Resolver($cache);
		$app = new \Asgard\Container\Container([
			'errorHandler' => new \Asgard\Debug\ErrorHandler,
			'hooks' => new \Asgard\Hook\HooksManager,
			'resolver' => $resolver,
		]);

		$httpKernel = new HttpKernel($app);
		$httpKernel->filterAfterAll(function($controller, $request, &$result){$result = 'foo!';});
		$resolver->addRoute(new Route('home', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$request = new \Asgard\Http\Request();
		$request->url->url = 'home';
		$this->assertEquals('foo!', $httpKernel->process($request, false)->getContent());
	}

	public function testAfter() {
		$cache = new \Asgard\Cache\Cache;
		$resolver = new \Asgard\Http\Resolver($cache);
		$app = new \Asgard\Container\Container([
			'errorHandler' => new \Asgard\Debug\ErrorHandler,
			'hooks' => new \Asgard\Hook\HooksManager,
			'resolver' => $resolver,
		]);

		$httpKernel = new HttpKernel($app);
		$httpKernel->filterAfter(['actions'=>'Asgard\Http\Tests\Fixtures'], function($controller, $request, &$result){$result = 'foo!';});
		$resolver->addRoute(new Route('home', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$request = new \Asgard\Http\Request();
		$request->url->url = 'home';
		$this->assertEquals('foo!', $httpKernel->process($request, false)->getContent());
	}

	public function testLayout() {
		$app = new \Asgard\Container\Container;
		$app['hooks'] = new \Asgard\Hook\HooksManager($app);
		$controller = new \Asgard\Http\Tests\Fixtures\Controllers\FooController();
		$controller->addFilter(new \Asgard\Http\Filters\PageLayout(function($content) { return '<h1>'.$content.'</h1>'; }));
		$res = $controller->run('page', new Request);

		$this->assertEquals('<h1>hello!</h1>', $res->content);
	}
}