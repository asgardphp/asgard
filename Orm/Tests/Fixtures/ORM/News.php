<?php
namespace Asgard\Orm\Tests\Fixtures\ORM;

class News extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'title',
			'content',
			'score' => 'integer',
			'category' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\ORM\Category',
			],
			'author' => [
				'type' => 'entity',
				'entity' => 'Asgard\Orm\Tests\Fixtures\ORM\Author',
			],
		];
	}
}