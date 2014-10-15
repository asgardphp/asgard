<?php
namespace Asgard\Orm\Tests\Fixtures;

class Author extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'post' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\Post',
				'many' => true,
			]
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];
	}
}