<?php
namespace Asgard\Entity\Tests\Fixtures;

class EntityWithFile extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'files' => [
				'type' => 'file',
				'many' => true
			],
			'file' => [
				'type' => 'file',
				'web'  => true,
			]
		];
	}
}