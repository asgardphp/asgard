<?php
namespace Asgard\Http\Tests;

use \Asgard\Http\HttpKernel;
use \Asgard\Http\Request;

class HttpKernelTest extends \PHPUnit_Framework_TestCase {
	public function testRunAndLastRequest() {
		$kernel = new HttpKernel;
		$kernel->setHooksManager(new \Asgard\Hook\HooksManager);

		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute'], [new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache)]);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new \Asgard\Http\Route('', 'Asgard\Http\Tests\Fixtures\HomeController', 'home')));
		$kernel->setResolver($resolver);

		$this->assertEquals('<h1>Asgard</h1><p>Hello!</p>', $kernel->run()->getContent());
	}

	public function testLambdaController() {
		$kernel = new HttpKernel;
		$kernel->setHooksManager(new \Asgard\Hook\HooksManager);

		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute'], [new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache)]);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new \Asgard\Http\Route('', 'Asgard\Http\LambdaController', function($request) { return '<h1>Asgard</h1><p>Hello!</p>'; })));
		$kernel->setResolver($resolver);

		$this->assertEquals('<h1>Asgard</h1><p>Hello!</p>', $kernel->process(new \Asgard\Http\Request, false)->getContent());
	}

	public function testCatching() {
		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute'], [new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache)]);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new \Asgard\Http\Route('', 'Asgard\Http\Tests\Fixtures\HomeController', 'error')));

		$kernel = new HttpKernel;
		$kernel->setHooksManager(new \Asgard\Hook\HooksManager);
		$kernel->setDebug(true);
		$kernel->setResolver($resolver);
		$kernel->setErrorHandler(new \Asgard\Debug\ErrorHandler);
		$response = $kernel->process(new Request, true);
		$this->assertEquals(500, $response->getCode());
		$this->assertContains('Undefined variable: a', $response->content);
	}

	public function testNoInformation() {
		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute'], [new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache)]);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new \Asgard\Http\Route('', 'Asgard\Http\Tests\Fixtures\HomeController', 'error')));

		$kernel = new HttpKernel;
		$kernel->setHooksManager(new \Asgard\Hook\HooksManager);
		$kernel->setDebug(false);
		$kernel->setResolver($resolver);
		$kernel->setErrorHandler(new \Asgard\Debug\ErrorHandler);
		$response = $kernel->process(new Request, true);
		$this->assertEquals(500, $response->getCode());
		$this->assertEquals('<h1>Error</h1>Oops, something went wrong.', $response->content);
	}

	public function testHookException() {
		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute'], [new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache)]);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new \Asgard\Http\Route('', 'Asgard\Http\Tests\Fixtures\HomeController', 'exception')));

		$hooks = new \Asgard\Hook\HooksManager;
		$hooks->hook('Asgard.Http.Exception.Asgard\Http\Tests\NotFoundException', function($chain, $e, &$response, $request) {
			$response = 'plplpl';
		});
		$kernel = new HttpKernel;
		$kernel->setHooksManager($hooks);
		$kernel->setResolver($resolver);
		$kernel->setDebug(false);
		$kernel->setErrorHandler(new \Asgard\Debug\ErrorHandler);
		$response = $kernel->process(new Request, true);
		$this->assertEquals('plplpl', $response);
	}
}

class NotFoundException extends \Exception {}