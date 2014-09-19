<?php
namespace Asgard\Orm\Tests\Entities;

class News extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'title',
			'content',
			'category' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Entities\Category',
			],
			'author' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Entities\Author',
			],
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];
	}
}