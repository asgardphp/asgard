<?php
namespace Asgard\Entity\Tests\Classes;

class Commenti18n extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'content' => [
				'i18n' => true
			],
			'comments' => [
				'type' => 'entity',
				'entity' => 'Asgard\Entity\Tests\Classes\News',
			],
			'comments' => [
				'type' => 'entity',
				'entity' => 'Asgard\Entity\Tests\Classes\Newsi18n',
			]
		];

		$definition->addProperty('another_property');

		$definition->behaviors = [
			new TestBehavior
		];
	}

	public function __toString() {
		return $this->title;
	}
}