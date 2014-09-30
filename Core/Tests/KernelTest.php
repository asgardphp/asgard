<?php
namespace Asgard\Core\Tests;

class KernelTest extends \PHPUnit_Framework_TestCase {
	public function testDefaultEnv() {

		$kernel = new \Asgard\Core\Kernel();
		$kernel->set('env', 'plpl');
		$kernel->load();
		$this->assertEquals('plpl', $kernel->getEnv());

		$kernel = new \Asgard\Core\Kernel();
		define('_ENV_', 'plpl');
		$kernel->load();
		$this->assertEquals('plpl', $kernel->getEnv());
	}

	public function testLoad() {
		$kernel = new \Asgard\Core\Kernel();
		$container = $kernel->getContainer();

		$kernel->load();
		$this->assertInstanceOf('Asgard\Config\Config', $container['config']);
	}
}
