<?php
namespace Asgard\Http\Tests\Fixtures;

class HomeController extends \Asgard\Http\Controller {
	/**
	@Route("")
	**/
	public function homeAction($request) {
		return '<h1>Asgard</h1><p>Hello!</p>';
	}

	public function errorAction() {
		echo $a;
	}

	public function exceptionAction() {
		throw new \Asgard\Http\Tests\NotFoundException;
	}
}