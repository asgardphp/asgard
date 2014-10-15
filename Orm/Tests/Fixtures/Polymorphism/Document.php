<?php
namespace Asgard\Orm\Tests\Fixtures\Polymorphism;

class Document extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'title',
			'user' => [
				'type'        => 'entity',
				'entity'      => 'Asgard\Orm\Tests\Fixtures\Polymorphism\User',
				'relation_type' => 'belongsTo'
			]
		];
	}
}