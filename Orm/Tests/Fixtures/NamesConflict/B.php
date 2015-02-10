<?php
namespace Asgard\Orm\Tests\Fixtures\NamesConflict;

class B extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'parent' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\NamesConflict\C',
			],
			'childs' => [
				'many' => true,
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\NamesConflict\A',
			],
		];
	}
}