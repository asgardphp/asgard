<?php
namespace Asgard\Entity\Tests\Classes;

class Comment extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'content',
			'published' => 'date',
			'comments' => [
				'type' => 'entity',
				'entity' => 'Asgard\Entity\Tests\Classes\News',
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