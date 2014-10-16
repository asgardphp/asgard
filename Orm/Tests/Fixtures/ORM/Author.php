<?php
namespace Asgard\Orm\Tests\Fixtures\ORM;

class Author extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'news' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\ORM\News',
				'many' => true,
			],
		];
	}
}