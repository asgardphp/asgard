<?php
namespace Asgard\Tester;

interface TestBuilderInterface {
	/**
	 * Add a new tests file.
	 * @param  array   $tests
	 * @param  string  $name
	 * @return boolean
	 */
	public function buildTests($tests, $name);
}