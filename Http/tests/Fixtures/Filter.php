<?php
namespace Asgard\Http\Tests\Fixtures;

class Filter extends \Asgard\Http\Filter {
	public function before(\Asgard\Http\Controller $controller, \Asgard\Http\Request $request) {
		return 'foo!';
	}
}