<?php
namespace Asgard\Orm\Tests\Fixtures\ORM;

class Category extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'title',
			'description',
			'news' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\ORM\News',
				'many' => true,
				'ormValidation' => [
					'relationrequired',
					'morethan' => 3
				]
			],
		];
	}
}