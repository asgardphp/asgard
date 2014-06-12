<?php
namespace Asgard\Hook\Tests;

use \Asgard\Http\View;
use \Asgard\Http\Viewable;

class ViewTest extends \PHPUnit_Framework_TestCase {
	public function testView() {
		$view = new View(__DIR__.'/Fixtures/template.php', ['test' => 'hello!']);
		$this->assertEquals('<h1>hello!</h1>', $view->render());
		$this->assertEquals('<h1>hello!</h1>', View::renderTemplate(__DIR__.'/Fixtures/template.php', ['test' => 'hello!']));
	}

	public function testViewable() {
		$this->assertEquals('<h1>hello world!</h1>', Viewable::widget('Asgard\Hook\Tests\_Viewable', 'test'));
	}
}

class _Viewable extends Viewable {
	public function test() {
		$this->test = 'hello world!';
		$this->setView(__DIR__.'/Fixtures/template.php');
	}
}