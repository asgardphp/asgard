<?php
namespace Asgard\Orm\Tests\Fixtures\Polymorphism;

class User extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'documents' => [
				'type'        => 'entity',
				'polymorphic' => true,
				'many'        => true,
				'classes'     => ['Asgard\Orm\Tests\Fixtures\Polymorphism\Document'],
				'relation_type' => 'hasMany'
			]
		];
	}
}