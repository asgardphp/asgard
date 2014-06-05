<?php
namespace Asgard\Http\Tests\Fixtures;

class HomeController extends \Asgard\Http\Controller {
	/**
	@Route("")
	**/
	public function homeAction($request) {
		return '<h1>Asgard</h1><p>Hello!</p>';
	}
}