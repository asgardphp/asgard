<?php
namespace Asgard\Core\Tests\Classes;

class Newsi18n extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
			'title' => array(
				'i18n' => true,
			),
			'content'
		);

		$definition->behaviors = array(
			new \Asgard\Behaviors\PageBehavior
		);

		$definition->addProperty('another_property');
	}

	public function __toString() {
		return $this->title;
	}
}