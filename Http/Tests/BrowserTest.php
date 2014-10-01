<?php
namespace Asgard\Http\Tests;

class BrowserTest extends \PHPUnit_Framework_TestCase {
	public function test1() {
		$cache = new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache);
		$resolver = new \Asgard\Http\Resolver($cache);
		$resolver->addRoute(new \Asgard\Http\Route('', 'Asgard\Http\Tests\Fixtures\HomeController', 'home'));
		$httpKernel = new \Asgard\Http\HttpKernel;
		$httpKernel->setResolver($resolver);
		$httpKernel->setHooksManager(new \Asgard\Hook\HooksManager);
		$browser = new \Asgard\Http\Browser\Browser($httpKernel);
		$this->assertEquals('<h1>Asgard</h1><p>Hello!</p>', $browser->get('')->getContent());
	}
}