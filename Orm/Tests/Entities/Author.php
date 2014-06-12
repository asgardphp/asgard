<?php
namespace Asgard\Orm\Tests\Entities;

class Author extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'name',
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];

		$definition->relations = [
			'news' => [
				'entity' => 'Asgard\Orm\Tests\Entities\News',
				'has' => 'many'
			],
		];
	}
}