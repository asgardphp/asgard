<?php
namespace Asgard\Entity\Tests\Fixtures;

class EntityWithFile extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'name',
			'files' => [
				'type' => 'file',
				'multiple' => true
			],
			'file' => 'file',
		];
	}
}