<?php
namespace Asgard\Http\Tests;

class BrowserTest extends \PHPUnit_Framework_TestCase {
	public function test1() {
		$resolver = new \Asgard\Http\Resolver;
		$resolver->addRoute(new \Asgard\Http\Route('', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$httpKernel = new \Asgard\Http\HttpKernel;
		$httpKernel->setResolver($resolver);
		$httpKernel->setHookManager(new \Asgard\Hook\HookManager);
		$browser = new \Asgard\Http\Browser\Browser($httpKernel);
		$this->assertEquals('<h1>Asgard</h1><p>Hello!</p>', $browser->get('')->getContent());
	}
}