<?php
namespace Asgard\Core\Tests\Classes;

class TestBehavior extends \Asgard\Entity\Behavior {
	public function static_test1() {
		return 'bla';
	}

	public function call_test2(\Asgard\Entity\Entity $entity) {
		return $entity->title;
	}
}