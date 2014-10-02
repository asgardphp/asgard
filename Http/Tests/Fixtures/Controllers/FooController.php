<?php
namespace Asgard\Http\Tests\Fixtures\Controllers;

class FooController extends \Asgard\Http\Controller {
	/**
	 * @Route(host = "example.com", method = "get", name = "foo", value = "page/:id", requirements = {
	 * 	"src" : {
	 * 		"type" : "regex",
	 * 		"regex" : ".+"
	 * 	}
	* })
	*/
	public function pageAction(\Asgard\Http\Request $request) {
		return 'hello!';
	}

	public function jsonAction(\Asgard\Http\Request $request) {
		return [
			new Entity(['title'=>'hello', 'content'=>'world']),
			new Entity(['title'=>'welcome', 'content'=>'home']),
		];
	}
}