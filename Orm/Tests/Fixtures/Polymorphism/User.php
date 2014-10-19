<?php
namespace Asgard\Orm\Tests\Fixtures\Polymorphism;

class User extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'documents' => [
				'type'        => 'entity',
				'entities'     => ['Asgard\Orm\Tests\Fixtures\Polymorphism\Document'],
				'many'        => true,
			]
		];
	}
}