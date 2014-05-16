<?php
namespace Asgard\Orm\Tests\Entities;

class Category extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
			'title',
			'description',
		);

		$definition->behaviors = array(
			new \Asgard\Orm\ORMBehavior
		);

		$definition->relations = array(
			'news' => array(
				'entity' => 'Asgard\Orm\Tests\Entities\News',
				'has' => 'many',
				'validation' => array(
					'relationrequired',
					'morethan' => 3
				)
			),
		);
	}
}