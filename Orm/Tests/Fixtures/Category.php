<?php
namespace Asgard\Orm\Tests\Fixtures;

class Category extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
			'name'
		);

		$definition->relations = array(
			'posts' => array(
				'entity' => 'Asgard\Orm\Tests\Fixtures\Post',
				'has' => 'many'
			)
		);

		$definition->behaviors = array(
			new \Asgard\Orm\ORMBehavior
		);
	}
}