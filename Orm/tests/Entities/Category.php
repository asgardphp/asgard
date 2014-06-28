<?php
namespace Asgard\Orm\Tests\Entities;

class Category extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'title',
			'description',
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];

		$definition->relations = [
			'news' => [
				'entity' => 'Asgard\Orm\Tests\Entities\News',
				'has' => 'many',
				'validation' => [
					'relationrequired',
					'morethan' => 3
				]
			],
		];
	}
}