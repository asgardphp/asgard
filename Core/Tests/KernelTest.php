<?php
namespace Asgard\Core\Tests;

class KernelTest extends \PHPUnit_Framework_TestCase {
	public function testDefaultEnv() {

		$kernel = new \Asgard\Core\Kernel();
		$kernel['env'] = 'plpl';
		$kernel->load();
		$this->assertEquals('plpl', $kernel->getEnv());

		$kernel = new \Asgard\Core\Kernel();
		define('_ENV_', 'plpl');
		$kernel->load();
		$this->assertEquals('plpl', $kernel->getEnv());
	}

	public function testLoad() {
		$kernel = new \Asgard\Core\Kernel();
		$app = $kernel->getApp();

		$kernel->load();
		$this->assertInstanceOf('Asgard\Config\Config', $app['config']);
	}
}
