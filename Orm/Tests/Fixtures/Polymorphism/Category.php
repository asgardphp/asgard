<?php
namespace Asgard\Orm\Tests\Fixtures\Polymorphism;

class Category extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'articles' => [
				'type'          => 'entity',
				'polymorphic'   => true,
				'as'            => 'categorisable',
				'many'          => true,
				'relation_type' => 'HMABT'
			]
		];
	}
}