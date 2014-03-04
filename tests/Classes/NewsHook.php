<?php
namespace Coxis\Core\Tests\Classes;

class NewsHook extends \Coxis\Core\Entity {
	public static $properties = array(
		'title' => array(
			'setHook' => array('Coxis\Core\Tests\Classes\NewsHook', 'reverse'),
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