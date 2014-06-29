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
		$app = new \Asgard\Container\Container([
			'hooks' => new \Asgard\Hook\HooksManager,
		]);
		$kernel = new HttpKernel($app);
		$app->register('templateEngine', function($app, $controller) {
			$engine = new Fixtures\Templates\TemplateEngine;
			$engine->setController($controller);
			return $engine;
		});

		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute'], [new \Asgard\Cache\NullCache]);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new Route('', 'Asgard\Http\Tests\Fixtures\TemplateController', 'home')));
		$app['resolver'] = $resolver;
		
		$this->assertEquals('<div>home!</div>', $kernel->process(new Request, false)->getContent());
	}

	public function testTemplateEngine() {
		$app = new \Asgard\Container\Container([
			'hooks' => new \Asgard\Hook\HooksManager,
		]);
		$kernel = new HttpKernel($app);
		$app->register('templateEngine', function($app, $controller) {
			$engine = new Fixtures\Templates\TemplateEngine;
			$engine->setController($controller);
			return $engine;
		});

		$resolver = $this->getMock('Asgard\Http\Resolver', ['getRoute'], [new \Asgard\Cache\NullCache]);
		$resolver->expects($this->once())->method('getRoute')->will($this->returnValue(new Route('', 'Asgard\Http\Tests\Fixtures\TemplateController', 'home2')));
		$app['resolver'] = $resolver;
		
		$this->assertEquals('<div>home!</div>', $kernel->process(new Request, false)->getContent());
	}
}