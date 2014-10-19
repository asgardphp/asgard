<?php
namespace Asgard\Orm\Tests\Fixtures\Polymorphism;

class Category extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'articles' => [
				'type'          => 'entity',
				'entities'   => ['Asgard\Orm\Tests\Fixtures\Polymorphism\Article3'],
				// 'as'            => 'categorisable',
				'many'          => true,
			]
		];
	}
}