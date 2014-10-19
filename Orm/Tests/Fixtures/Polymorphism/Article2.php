<?php
namespace Asgard\Orm\Tests\Fixtures\Polymorphism;

class Article2 extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'title',
			'author' => [
				'type'        => 'entity',
				'entity'      => 'Asgard\Orm\Tests\Fixtures\Polymorphism\Author',
			]
		];
	}
}