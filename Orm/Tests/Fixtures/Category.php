<?php
namespace Asgard\Orm\Tests\Fixtures;

class Category extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'posts' => [
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