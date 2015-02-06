<?php
namespace Asgard\Orm\Tests\Fixtures\ORM;

class B extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'a' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\ORM\A',
			],
		];
	}
}