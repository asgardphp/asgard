<?php
namespace Asgard\Orm\Tests\Fixtures\Datamapper;

class News extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'title',
			'content',
			'category' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\Datamapper\Category',
			],
			'author' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\Datamapper\Author',
			],
		];
	}
}