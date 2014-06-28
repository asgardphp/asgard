<?php
namespace Asgard\Orm\Tests\Fixtures;

class Category extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'name'
		];

		$definition->relations = [
			'posts' => [
				'entity' => 'Asgard\Orm\Tests\Fixtures\Post',
				'has' => 'many'
			]
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];
	}
}