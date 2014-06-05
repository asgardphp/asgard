<?php
namespace Asgard\Hook\Tests;

use \Asgard\Http\HttpKernel;
use \Asgard\Http\Request;

class HttpKernelTest extends \PHPUnit_Framework_TestCase {
	public function testRunAndLastRequest() {
		$app = new \Asgard\Core\App(array(
			'hooks' => new \Asgard\Hook\HooksManager,
		));
		$kernel = new HttpKernel($app);

		$resolver = $this->getMock('Asgard\Http\Resolver', array('getCallback', 'getArguments'), array(new \Asgard\Cache\NullCache));
		$resolver->expects($this->once())->method('getCallback')->will($this->returnValue(function() use($kernel, $app) { 
			$this->assertInstanceOf('Asgard\Http\Request', $kernel->getLastRequest());
			$this->assertInstanceOf('Asgard\Http\Request', $app['request']);
			return 'response';
		}));
		$resolver->expects($this->once())->method('getArguments')->will($this->returnValue(array()));
		$app['resolver'] = $resolver;
		
		$this->assertEquals('response', $kernel->run());
	}

	public function testCatching() {
		$resolver = $this->getMock('Asgard\Http\Resolver', array('getCallback', 'getArguments'), array(new \Asgard\Cache\NullCache));
		$resolver->expects($this->once())->method('getCallback')->will($this->returnValue(function() { 
			echo $a;
		}));
		$resolver->expects($this->once())->method('getArguments')->will($this->returnValue(array()));

		$kernel = new HttpKernel(array(
			'hooks' => new \Asgard\Hook\HooksManager,
			'errorHandler' => new \Asgard\Debug\ErrorHandler,
			'resolver' => $resolver,
			'config' => array(
				'debug' => true
			)
		));
		$response = $kernel->process(new Request, true);
		$this->assertEquals(500, $response->getCode());
		$this->assertContains('Undefined variable: a', $response->content);
	}

	public function testNoInformation() {
		$resolver = $this->getMock('Asgard\Http\Resolver', array('getCallback', 'getArguments'), array(new \Asgard\Cache\NullCache));
		$resolver->expects($this->once())->method('getCallback')->will($this->returnValue(function() { 
			echo $a;
		}));
		$resolver->expects($this->once())->method('getArguments')->will($this->returnValue(array()));

		$kernel = new HttpKernel(array(
			'hooks' => new \Asgard\Hook\HooksManager,
			'errorHandler' => new \Asgard\Debug\ErrorHandler,
			'resolver' => $resolver,
			'config' => array(
				'debug' => false
			),
			'translator' => new \Symfony\Component\Translation\Translator('en'),
		));
		$response = $kernel->process(new Request, true);
		$this->assertEquals(500, $response->getCode());
		$this->assertEquals('<h1>Error</h1>Oops, something went wrong.', $response->content);
	}

	public function testHookException() {
		$resolver = $this->getMock('Asgard\Http\Resolver', array('getCallback', 'getArguments'), array(new \Asgard\Cache\NullCache));
		$resolver->expects($this->once())->method('getCallback')->will($this->returnValue(function() { 
			throw new NotFoundException;
		}));
		$resolver->expects($this->once())->method('getArguments')->will($this->returnValue(array()));

		$hook = new \Asgard\Hook\HooksManager;
		$hook->hook('Asgard.Http.Exception.Asgard\Hook\Tests\NotFoundException', function($chain, $e, &$response, $request) {
			$response = 'plplpl';
		});
		$kernel = new HttpKernel(array(
			'hooks' => $hook,
			'errorHandler' => new \Asgard\Debug\ErrorHandler,
			'resolver' => $resolver,
			'config' => array(
				'debug' => false
			),
			'translator' => new \Symfony\Component\Translation\Translator('en'),
		));
		$response = $kernel->process(new Request, true);
		$this->assertEquals('plplpl', $response);
	}
}

class NotFoundException extends \Exception {}