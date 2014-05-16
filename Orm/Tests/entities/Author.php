<?php
namespace Asgard\Orm\Tests\Entities;

class Author extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
			'name',
		);

		$definition->behaviors = array(
			new \Asgard\Orm\ORMBehavior
		);

		$definition->relations = array(
			'news' => array(
				'entity' => 'Asgard\Orm\Tests\Entities\News',
				'has' => 'many'
			),
		);
	}
}