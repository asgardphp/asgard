<?php
namespace Asgard\Orm\Tests\Fixtures\Polymorphism;

class Author extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'article' => [
				'type'        => 'entity',
				'polymorphic' => true,
				'relation_type' => 'hasOne'
			]
		];
	}
}