<?php
namespace Asgard\Orm\Tests\Fixtures\I18n;

class Comment extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'title',
			'news'	=>	[
				'type' => 'entity',
				'entity'	=>	'\Asgard\Orm\Tests\Fixtures\I18n\News',
			],
		];
	}
}