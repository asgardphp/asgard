<?php
namespace Asgard\Orm\Tests\Fixtures\I18n;

class News extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
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
				'entity'	=>	'\Asgard\Orm\Tests\Fixtures\I18n\Comment',
				'many' => true,
			],
		];
	}
}