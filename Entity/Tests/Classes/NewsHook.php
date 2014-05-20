<?php
namespace Asgard\Entity\Tests\Classes;

class NewsHook extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
			'title' => array(
				'setHook' => array('Asgard\Entity\Tests\Classes\NewsHook', 'reverse'),
			),
		);

		$definition->addProperty('another_property');
	}

	public function __toString() {
		return $this->title;
	}

	public static function reverse($a) {
		return strrev($a);
	}
}