<?php
namespace Asgard\Config\Tests;

class ConfigTest extends \PHPUnit_Framework_TestCase {
	public function testloadDir() {
		$config = new \Asgard\Config\Config;

		$config->loadDir(__DIR__.'/fixtures/config/');

		$this->assertEquals(123, $config['a']['b']['c']);
	}
}