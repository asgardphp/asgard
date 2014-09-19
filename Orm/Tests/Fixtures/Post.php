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
			],
			'author' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\Author',
			],
			'categories' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\Category',
				'many' => true,
			],
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];
	}
}