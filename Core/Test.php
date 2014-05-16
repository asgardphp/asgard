<?php
namespace Asgard\Core;

class Test extends \PHPUnit_Framework_TestCase {
	protected function getBrowser() {
		$browser = new \Asgard\Utils\TestBrowser;
		return $browser;
	}
}