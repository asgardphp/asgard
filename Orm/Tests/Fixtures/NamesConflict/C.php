<?php
namespace Asgard\Orm\Tests\Fixtures\NamesConflict;

class C extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'childs' => [
				'many' => true,
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\NamesConflict\B',
			],
		];
	}
}