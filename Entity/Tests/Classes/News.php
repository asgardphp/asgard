<?php
namespace Asgard\Core\Tests\Classes;

class News extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
			'title' => array(
				'validation' => array(
					'required' => true,
				)
			),
			'content',
			'published' => 'date'
		);

		$definition->addProperty('another_property');

		$definition->behaviors = array(
			new TestBehavior
		);
	}

	public function __toString() {
		return $this->title;
	}
}