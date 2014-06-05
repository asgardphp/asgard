<?php
namespace Asgard\Hook\Tests;

use \Asgard\Http\Request;
use \Asgard\Http\URL;
// use \Asgard\Http\Resolver;
// use \Asgard\Http\Route;

class UrlTest extends \PHPUnit_Framework_TestCase {
	public function testFull() {
		$request = new Request;
		$request->get->set(array(
			'a' => '1',
			'b' => '2'
		));
		$url = new URL($request);
		$url->setURL('plplpl');
		$url->setHost('myhost.com');
		$url->setRoot('website');
		$this->assertEquals('http://myhost.com/website/plplpl', $url->current());
		$this->assertEquals('http://myhost.com/website/plplpl?a=1&b=2', $url->full());
		$this->assertEquals('http://myhost.com/website/plplpl?a=1&b=3', $url->full(array('b'=>3)));
	}

	public function testTo() {
		$request = new Request;
		$request->get->set(array(
			'a' => '1',
			'b' => '2'
		));
		$url = new URL($request);
		$url->setURL('plplpl');
		$url->setHost('myhost.com');
		$url->setRoot('website');

		$this->assertEquals('http://myhost.com/website/a_page', $url->to('a_page'));
	}

	public function testStartsWith() {
		$request = new Request;
		$request->get->set(array(
			'a' => '1',
			'b' => '2'
		));
		$url = new URL($request);
		$url->setURL('admin/a_page');
		$url->setHost('myhost.com');
		$url->setRoot('website');

		$this->assertTrue($url->startsWith('admin'));
		$this->assertFalse($url->startsWith('plpl'));
	}
}
