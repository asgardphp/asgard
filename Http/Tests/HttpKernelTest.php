<?php
namespace Asgard\Http\Tests;

use \Asgard\Http\HttpKernel;
use \Asgard\Http\Request;

class HttpKernelTest extends \PHPUnit_Framework_TestCase {
	public function testRunAndLastRequest() {
		$container = new \Asgard\Container\Container([
			'hooks' => new \Asgard\Hook\HooksManager,
		]);
		$kernel = new HttpKernel($container);

		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute'], [new \Asgard\Cache\NullCache]);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new \Asgard\Http\Route('', 'Asgard\Http\Tests\Fixtures\HomeController', 'home')));
		$container['resolver'] = $resolver;
		
		$this->assertEquals('<h1>Asgard</h1><p>Hello!</p>', $kernel->run()->getContent());
	}

	public function testLambdaController() {
		$container = new \Asgard\Container\Container([
			'hooks' => new \Asgard\Hook\HooksManager,
		]);
		$kernel = new HttpKernel($container);

		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute'], [new \Asgard\Cache\NullCache]);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new \Asgard\Http\Route('', 'Asgard\Http\LambdaController', function($request) { return '<h1>Asgard</h1><p>Hello!</p>'; })));
		$container['resolver'] = $resolver;
		
		$this->assertEquals('<h1>Asgard</h1><p>Hello!</p>', $kernel->process(new \Asgard\Http\Request, false)->getContent());
	}

	public function testCatching() {
		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute'], [new \Asgard\Cache\NullCache]);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new \Asgard\Http\Route('', 'Asgard\Http\Tests\Fixtures\HomeController', 'error')));

		$kernel = new HttpKernel([
			'hooks' => new \Asgard\Hook\HooksManager,
			'errorHandler' => new \Asgard\Debug\ErrorHandler,
			'resolver' => $resolver,
			'config' => [
				'debug' => true
			]
		]);
		$response = $kernel->process(new Request, true);
		$this->assertEquals(500, $response->getCode());
		$this->assertContains('Undefined variable: a', $response->content);
	}

	public function testNoInformation() {
		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute'], [new \Asgard\Cache\NullCache]);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new \Asgard\Http\Route('', 'Asgard\Http\Tests\Fixtures\HomeController', 'error')));

		$kernel = new HttpKernel([
			'hooks' => new \Asgard\Hook\HooksManager,
			'errorHandler' => new \Asgard\Debug\ErrorHandler,
			'resolver' => $resolver,
			'config' => [
				'debug' => false
			],
		]);
		$response = $kernel->process(new Request, true);
		$this->assertEquals(500, $response->getCode());
		$this->assertEquals('<h1>Error</h1>Oops, something went wrong.', $response->content);
	}

	public function testHookException() {
		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute'], [new \Asgard\Cache\NullCache]);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new \Asgard\Http\Route('', 'Asgard\Http\Tests\Fixtures\HomeController', 'exception')));

		$hook = new \Asgard\Hook\HooksManager;
		$hook->hook('Asgard.Http.Exception.Asgard\Http\Tests\NotFoundException', function($chain, $e, &$response, $request) {
			$response = 'plplpl';
		});
		$kernel = new HttpKernel([
			'hooks' => $hook,
			'errorHandler' => new \Asgard\Debug\ErrorHandler,
			'resolver' => $resolver,
			'config' => [
				'debug' => false
			],
		]);
		$response = $kernel->process(new Request, true);
		$this->assertEquals('plplpl', $response);
	}
}

class NotFoundException extends \Exception {}