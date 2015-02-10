<?php
namespace Asgard\Orm\Tests\Fixtures\NamesConflict;

class A extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'parent' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\NamesConflict\B',
			],
		];
	}
}