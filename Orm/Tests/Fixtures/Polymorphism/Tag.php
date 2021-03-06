<?php
namespace Asgard\Orm\Tests\Fixtures\Polymorphism;

class Tag extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'article' => [
				'type'        => 'entity',
				'entities' => ['Asgard\Orm\Tests\Fixtures\Polymorphism\Article'],
			]
		];
	}
}