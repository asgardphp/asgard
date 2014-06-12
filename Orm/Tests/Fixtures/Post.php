<?php
namespace Asgard\Orm\Tests\Fixtures;

class Post extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'title' => [
				'orm' => [
					'default' => 'a',
					'nullable' => false,
					'key' => 'UNI'
				]
			],
			'posted' => 'date',
			'content' => [
				'type' => 'longtext',
				'i18n' => true
			]
		];

		$definition->relations = [
			'author' => [
				'entity' => 'Asgard\Orm\Tests\Fixtures\Author',
				'has' => 'one'
			],
			'categories' => [
				'entity' => 'Asgard\Orm\Tests\Fixtures\Category',
				'has' => 'many'
			],
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];
	}
}