<?php
namespace Asgard\Orm\Tests\Fixtures\Migrations;

class Author2 extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'post' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\Migrations\Post2',
				'many' => true,
			]
		];

		$definition->table = 'author';

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];
	}
}