<?php
namespace Asgard\Orm\Tests\Fixtures;

class Author extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'name'
		];

		$definition->relations = [
			'post' => [
				'entity' => 'Asgard\Orm\Tests\Fixtures\Post',
				'has' => 'many'
			]
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];
	}
}