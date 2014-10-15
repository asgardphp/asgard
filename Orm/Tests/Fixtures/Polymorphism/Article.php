<?php
namespace Asgard\Orm\Tests\Fixtures\Polymorphism;

class Article extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'title',
			'tags' => [
				'type'        => 'entity',
				'entity'      => 'Asgard\Orm\Tests\Fixtures\Polymorphism\Tag',
				'many'        => true,
				'relation_type' => 'hasMany'
			]
		];
	}
}