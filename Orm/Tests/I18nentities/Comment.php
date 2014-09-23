<?php
namespace Asgard\Orm\Tests\I18nentities;

class Comment extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'title',
			'news'	=>	[
				'type' => 'entity',
				'entity'	=>	'\Asgard\Orm\Tests\I18nentities\News',
			],
		];
	}
}