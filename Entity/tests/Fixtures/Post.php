<?php
namespace Asgard\Entity\Tests\Fixtures;

class Post extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'title' => [
				'i18n',
				'validation' => 'minlength:5',
				'messages' => [
					'minlength' => ':attribute is too short.'
				]
			]
		];
	}
}