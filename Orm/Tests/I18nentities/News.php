<?php
namespace Asgard\Orm\Tests\I18nentities;

class News extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'title',
			'test'	=>	[
				'i18n'	=>	true,
				'validation' => [
					'required'	=>	false,
				]
			],
			'comments'	=>	[
				'type' => 'entity',
				'entity'	=>	'\Asgard\Orm\Tests\I18nentities\Comment',
				'many' => true,
			],
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];
	}
}