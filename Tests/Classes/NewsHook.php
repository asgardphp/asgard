<?php
namespace Asgard\Core\Tests\Classes;

class NewsHook extends \Asgard\Core\Entity {
	public static $properties = array(
		'title' => array(
			'setHook' => array('Asgard\Core\Tests\Classes\NewsHook', 'reverse'),
		),
	);

	public function __toString() {
		return $this->title;
	}

	public static function configure($definition) {
		$definition->addProperty('another_property');
	}

	public static function reverse($a) {
		return strrev($a);
	}
}