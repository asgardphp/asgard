<?php
namespace Asgard\Hook\Tests\Fixtures;

class Hooks extends \Asgard\Hook\HooksContainer {
	/**
	@Hook("test")
	*/
	public static function foo() {
		return 'bar';
	}
}