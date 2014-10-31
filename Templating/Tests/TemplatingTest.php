<?php
namespace Asgard\Templating\Tests;

use \Asgard\Templating\PHPTemplate;
use \Asgard\Templating\ViewableTrait;

class TemplatingTest extends \PHPUnit_Framework_TestCase {
	public function testPHPTemplate() {
		$view = new PHPTemplate(__DIR__.'/fixtures/template.php', ['test' => 'hello!']);
		$this->assertEquals('<h1>hello!</h1>', $view->render());
		$this->assertEquals('<h1>hello!</h1>', PHPTemplate::renderFile(__DIR__.'/fixtures/template.php', ['test' => 'hello!']));
	}

	public function testViewable() {
		$this->assertEquals('<h1>hello world!</h1>', _Viewable::sFragment('test'));
	}

	public function testTemplatePathSolver() {
		$viewable = new _Viewable;
		$viewable->addTemplatePathSolver(function($viewable, $template) {
			return __DIR__.'/fixtures/'.$template.'.php';
		});
		$this->assertEquals('<h1>hello world, again!</h1>', $viewable->fragment('test1'));
	}

	public function testFragmentReturn() {
		$this->assertEquals('This is.. Viewable!', _Viewable::sFragment('test2'));
	}

	public function testFragmentEcho() {
		$this->assertEquals('This is.. Viewable!', _Viewable::sFragment('test3'));
	}
}

class _Viewable {
	use ViewableTrait;

	public function test() {
		$this->test = 'hello world!';
		$this->view = __DIR__.'/fixtures/template.php';
	}

	public function test1() {
		$this->test = 'hello world, again!';
		$this->view = 'template';
	}

	public static function test2() {
		return 'This is.. Viewable!';
	}

	public static function test3() {
		echo 'This is.. Viewable!';
	}
}