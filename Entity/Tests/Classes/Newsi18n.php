<?php
namespace Asgard\Entity\Tests\Classes;

class Newsi18n extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'title' => [
				'i18n' => true,
			],
			'content'
		];

		$definition->addProperty('another_property');
	}

	public function __toString() {
		return $this->title;
	}
}