<?php
namespace Asgard\Orm\Tests\I18nentities;

class Comment extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'title',
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];

		$definition->relations = [
			'news'	=>	[
				'entity'	=>	'\Asgard\Orm\Tests\I18nentities\News',
				'has'	=>	'one',
			],
		];
	}
}