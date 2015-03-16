<?php
namespace Asgard\Entity\Tests\Fixtures;

class EntityMultiple extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'names' => [
				'type' => 'string',
				'many' => true
			],
		];
	}
}