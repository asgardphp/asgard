<?php
namespace Asgard\Orm\Tests\Entities;

class Author extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'news' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Entities\News',
				'many' => true,
			],
		];

		// $definition->behaviors = [
		// 	new \Asgard\Orm\ORMBehavior
		// ];
	}
}