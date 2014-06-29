<?php
namespace Asgard\Http\Tests;

use \Asgard\Http\PHPTemplate;
use \Asgard\Http\Viewable;

class ViewTest extends \PHPUnit_Framework_TestCase {
	public function testView() {
		$view = new PHPTemplate(__DIR__.'/Fixtures/template.php', ['test' => 'hello!']);
		$this->assertEquals('<h1>hello!</h1>', $view->render());
		$this->assertEquals('<h1>hello!</h1>', PHPTemplate::renderFile(__DIR__.'/Fixtures/template.php', ['test' => 'hello!']));
	}

	public function testViewable() {
		$this->assertEquals('<h1>hello world!</h1>', \Asgard\Http\Tests\_Viewable::fragment('test'));
	}
}

class _Viewable {
	use Viewable;

	public function test() {
		$this->test = 'hello world!';
		$this->view = __DIR__.'/Fixtures/template.php';
	}
}