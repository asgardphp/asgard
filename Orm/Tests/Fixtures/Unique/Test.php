<?php
namespace Asgard\Orm\Tests\Fixtures\Unique;

class Test extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name' => [
				'validation' => 'unique'
			]
		];
	}
}