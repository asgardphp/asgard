<?php
namespace Asgard\Orm\Tests\Fixtures\Migrations;

class Post extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'title' => [
				'orm' => [
					'default' => 'a',
					'notnull' => true,
				]
			],
			'posted' => 'date',
			'content' => [
				'type' => 'text',
				'i18n' => true
			],
			'author' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\Migrations\Author',
			],
			'categories' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\Migrations\Category',
				'many' => true,
			],
		];

		$definition->orm = [
			'indexes' => [
				[
					'type' => 'unique',
					'columns' => ['title']
				]
			]
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];
	}
}