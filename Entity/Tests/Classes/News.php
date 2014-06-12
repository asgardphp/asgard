<?php
namespace Asgard\Entity\Tests\Classes;

class News extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'title' => [
				'validation' => [
					'required' => true,
				]
			],
			'content',
			'published' => 'date'
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