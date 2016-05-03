<?php
namespace Asgard\Orm\Tests\Fixtures\ProxyEntity;

class A extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name',
			'b' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\ProxyEntity\B',
			],
		];
	}
}