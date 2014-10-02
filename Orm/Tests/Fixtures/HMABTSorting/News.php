<?php
namespace Asgard\Orm\Tests\Fixtures\HMABTSorting;

class News extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'title',
			'tags' => [
				'type'   => 'entity',
				'many'   => true,
				'entity' => 'Asgard\Orm\Tests\Fixtures\HMABTSorting\Tag',
				'sortable' => true,
			]
		];
	}
}