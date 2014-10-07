<?php
namespace Asgard\Http\Tests;

use \Asgard\Http\HttpKernel;
use \Asgard\Http\Controller;
use \Asgard\Http\ControllerRoute;
use \Asgard\Http\Resolver;
use \Asgard\Http\Route;
use \Asgard\Http\Request;

class ControllerViewTest extends \PHPUnit_Framework_TestCase {
	public function testReturnTemplate() {
		$kernel = new HttpKernel;
		$kernel->setHooksManager(new \Asgard\Hook\HooksManager);
		$kernel->setTemplateEngineFactory(new \Asgard\Container\Factory(function($container, $controller) {
			$engine = new Fixtures\Templates\TemplateEngine;
			$engine->setController($controller);
			return $engine;
		}));

		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute']);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new Route('', 'Asgard\Http\Tests\Fixtures\TemplateController', 'home')));
		$kernel->setResolver($resolver);

		$this->assertEquals('<div>home!</div>', $kernel->process(new Request, false)->getContent());
	}

	public function testTemplateEngine() {
		$kernel = new HttpKernel;
		$kernel->setHooksManager(new \Asgard\Hook\HooksManager);
		$kernel->setTemplateEngineFactory(new \Asgard\Container\Factory(function($container, $controller) {
			$engine = new Fixtures\Templates\TemplateEngine;
			$engine->setController($controller);
			return $engine;
		}));

		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute']);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new Route('', 'Asgard\Http\Tests\Fixtures\TemplateController', 'home2')));
		$kernel->setResolver($resolver);

		$this->assertEquals('<div>home!</div>', $kernel->process(new Request, false)->getContent());
	}
}