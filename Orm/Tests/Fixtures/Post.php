<?php
namespace Asgard\Orm\Tests\Fixtures;

class Post extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
			'title' => array(
				'orm' => array(
					'default' => 'a',
					'nullable' => false,
					'key' => 'UNI'
				)
			),
			'posted' => 'date',
			'content' => array(
				'type' => 'longtext',
				'i18n' => true
			)
		);

		$definition->relations = array(
			'author' => array(
				'entity' => 'Asgard\Orm\Tests\Fixtures\Author',
				'has' => 'one'
			),
			'categories' => array(
				'entity' => 'Asgard\Orm\Tests\Fixtures\Category',
				'has' => 'many'
			),
		);

		$definition->behaviors = array(
			new \Asgard\Orm\ORMBehavior
		);
	}
}