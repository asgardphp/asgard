<?php
namespace Asgard\Orm\Tests\Entities;

class Category extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'title',
			'description',
			'news' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Entities\News',
				'many' => true,
				'validation' => [
					'relationrequired',
					'morethan' => 3
				]
			],
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];
	}
}