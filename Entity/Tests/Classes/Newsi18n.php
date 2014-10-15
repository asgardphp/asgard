<?php
namespace Asgard\Entity\Tests\Classes;

class Newsi18n extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'title' => [
				'i18n' => true,
			],
			'content',
			'comments' => [
				'type' => 'entity',
				'entity' => 'Asgard\Entity\Tests\Classes\Commenti18n',
				'many' => true,
			]
		];

		$definition->addProperty('another_property');
	}

	public function __toString() {
		return $this->title;
	}
}