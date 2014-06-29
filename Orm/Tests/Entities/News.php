<?php
namespace Asgard\Orm\Tests\Entities;

class News extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'title',
			'content',
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];

		$definition->relations = [
			'category' => [
				'entity' => 'Asgard\Orm\Tests\Entities\Category',
				'has' => 'one'
			],
			'author' => [
				'entity' => 'Asgard\Orm\Tests\Entities\Author',
				'has' => 'one'
			],
		];
	}
}