<?php
namespace Asgard\Hook\Tests;

use \Asgard\Http\Request;

class RequestTest extends \PHPUnit_Framework_TestCase {
	public function testCreateFromGlobals() {
		$_GET['a'] = 1;
		$_POST['b'] = 2;
		$_FILES = ['tmp_name'=>['c'=>'/path/to/file.jpg'], 'name'=>['c'=>'file.jpg'], 'error'=>['c'=>0], 'size'=>['c'=>10], 'type'=>['c'=>'image/jpg']];
		$_COOKIE['d'] = 4;
		$_SERVER['e'] = 5;
		$_SESSION['f'] = 6;
		$request = Request::createFromGlobals();

		$this->assertEquals($_GET['a'], $request->get['a']);
		$this->assertEquals($_POST['b'], $request->post['b']);
		$this->assertInstanceof('Asgard\Http\HttpFile', $request->file['c']);
		$this->assertEquals($_COOKIE['d'], $request->cookie['d']);
		$this->assertEquals($_SERVER['e'], $request->server['e']);
		$this->assertEquals($_SESSION['f'], $request->session['f']);

		$this->assertInstanceOf('Asgard\Http\URL', $request->url);
	}

	public function testBody() {
		$request = new Request;
		$body = '{"abc":123}';
		$request->setBody($body);
		$this->assertEquals($body, $request->getBody());
		$this->assertEquals(['abc'=>123], $request->getJSON());
	}
}
