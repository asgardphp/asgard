<?php
namespace Asgard\Entity\Tests\Classes;

class NewsHook extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'title' => [
				'setHook' => ['Asgard\Entity\Tests\Classes\NewsHook', 'reverse'],
			],
		];

		$definition->addProperty('another_property');
	}

	public function __toString() {
		return $this->title;
	}

	public static function reverse($a) {
		return strrev($a);
	}
}