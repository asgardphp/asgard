<?php
namespace Asgard\Core\Tests;

class ConfigTest extends \PHPUnit_Framework_TestCase {
	#todo a utiliser pour tester Utils\Bag
	// public function testLoad() {
	// 	$config = new \Asgard\Core\Config;

	// 	$config->load(array(
	// 		'a.b.c' => 123,
	// 	));

	// 	$this->assertEquals(123, $config['a']['b']['c']);
	// 	$this->assertEquals(123, $config['a.b.c']);
	// }

	public function testLoadConfigDir() {
		$config = new \Asgard\Core\Config;

		$config->loadConfigDir(__DIR__.'/Fixtures/config/');

		$this->assertEquals(123, $config['a']['b']['c']);
	}
}