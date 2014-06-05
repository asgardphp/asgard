<?php
namespace Asgard\Orm\Tests\Fixtures;

class Author extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
			'name'
		);

		$definition->relations = array(
			'post' => array(
				'entity' => 'Asgard\Orm\Tests\Fixtures\Post',
				'has' => 'many'
			)
		);

		$definition->behaviors = array(
			new \Asgard\Orm\ORMBehavior
		);
	}
}