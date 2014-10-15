<?php
namespace Asgard\Hook\Tests\Fixtures;

class Hooks extends \Asgard\Hook\HookContainer {
	/**
	 * @Hook("test")
	*/
	public static function foo() {
		return 'bar';
	}
}