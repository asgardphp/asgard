<?php
namespace Asgard\Orm\Tests\Entities;

class News extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
			'title',
			'content',
		);

		$definition->behaviors = array(
			new \Asgard\Orm\ORMBehavior
		);

		$definition->relations = array(
			'category' => array(
				'entity' => 'Asgard\Orm\Tests\Entities\Category',
				'has' => 'one'
			),
			'author' => array(
				'entity' => 'Asgard\Orm\Tests\Entities\Author',
				'has' => 'one'
			),
		);
	}
}