<?php
namespace Asgard\Http\Tests;

use \Asgard\Http\HttpKernel;
use \Asgard\Http\Request;

class HttpKernelTest extends \PHPUnit_Framework_TestCase {
	public function testRunAndLastRequest() {
		$kernel = new HttpKernel;
		$kernel->setHookManager(new \Asgard\Hook\HookManager);

		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute']);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new \Asgard\Http\Route('', 'Asgard\Http\Tests\Fixtures\HomeController', 'home')));
		$kernel->setResolver($resolver);

		$this->assertEquals('<h1>Asgard</h1><p>Hello!</p>', $kernel->run()->getContent());
	}

	public function testLambdaController() {
		$kernel = new HttpKernel;
		$kernel->setHookManager(new \Asgard\Hook\HookManager);

		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute']);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new \Asgard\Http\Route('', 'Asgard\Http\LambdaController', function($request) { return '<h1>Asgard</h1><p>Hello!</p>'; })));
		$kernel->setResolver($resolver);

		$this->assertEquals('<h1>Asgard</h1><p>Hello!</p>', $kernel->process(new \Asgard\Http\Request, false)->getContent());
	}

	public function testCatching() {
		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute']);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new \Asgard\Http\Route('', 'Asgard\Http\Tests\Fixtures\HomeController', 'error')));

		$kernel = new HttpKernel;
		$kernel->setHookManager(new \Asgard\Hook\HookManager);
		$kernel->setDebug(true);
		$kernel->setResolver($resolver);
		$errorHandler = new \Asgard\Debug\ErrorHandler;
		$errorHandler->setDisplay(false);
		$kernel->setErrorHandler($errorHandler);
		$response = $kernel->process(new Request, true);
		$this->assertEquals(500, $response->getCode());
		$this->assertContains('Undefined variable: a', $response->getContent());
	}

	public function testNoInformation() {
		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute']);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new \Asgard\Http\Route('', 'Asgard\Http\Tests\Fixtures\HomeController', 'error')));

		$kernel = new HttpKernel;
		$kernel->setHookManager(new \Asgard\Hook\HookManager);
		$kernel->setDebug(false);
		$kernel->setResolver($resolver);
		$errorHandler = new \Asgard\Debug\ErrorHandler;
		$errorHandler->setDisplay(false);
		$kernel->setErrorHandler($errorHandler);
		$response = $kernel->process(new Request, true);
		$this->assertEquals(500, $response->getCode());
		$this->assertEquals('<h1>Error</h1>Oops, something went wrong.', $response->getContent());
	}

	public function testHookException() {
		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute']);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new \Asgard\Http\Route('', 'Asgard\Http\Tests\Fixtures\HomeController', 'exception')));

		$hooks = new \Asgard\Hook\HookManager;
		$hooks->hook('Asgard.Http.Exception.Asgard\Http\Tests\NotFoundException', function($chain, $e, &$response, $request) {
			$response = 'plplpl';
		});
		$kernel = new HttpKernel;
		$kernel->setHookManager($hooks);
		$kernel->setResolver($resolver);
		$errorHandler = new \Asgard\Debug\ErrorHandler;
		$errorHandler->setDisplay(false);
		$kernel->setErrorHandler($errorHandler);
		$response = $kernel->process(new Request, true);
		$this->assertEquals('plplpl', $response);

		ob_end_clean();
	}
}

class NotFoundException extends \Exception {}