<?php
namespace Asgard\Orm\Tests\Fixtures\HMABTSorting;

class Tag extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'title',
			'news' => [
				'type'   => 'entity',
				'many'   => true,
				'entity' => 'Asgard\Orm\Tests\Fixtures\HMABTSorting\News',
			]
		];
	}
}