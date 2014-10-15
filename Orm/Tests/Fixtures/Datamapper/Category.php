<?php
namespace Asgard\Orm\Tests\Fixtures\Datamapper;

class Category extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'title',
			'description',
			'news' => [
				'type'       => 'entity',
				'entity'     => 'Asgard\Orm\Tests\Fixtures\Datamapper\News',
				'many'       => true,
				'validation' => [
					'relationrequired',
					'morethan' => 3
				]
			],
		];
	}
}