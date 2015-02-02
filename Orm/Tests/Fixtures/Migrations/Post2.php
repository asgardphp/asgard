<?php
namespace Asgard\Orm\Tests\Fixtures\Migrations;

class Post2 extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'title' => [
				'orm' => [
					'default' => 'b',
					'notnull' => false,
				]
			],
			'content2' => [
				'type' => 'longtext',
			],
			'author' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\Migrations\Author',
			],
		];

		$definition->table = 'post';

		$definition->orm = [
			'indexes' => [
				[
					'type' => 'index',
					'columns' => ['content2']
				]
			]
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];
	}
}