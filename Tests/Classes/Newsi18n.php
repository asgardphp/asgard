<?php
namespace Asgard\Core\Tests\Classes;

class Newsi18n extends \Asgard\Core\Entity {
	public static $properties = array(
		'title' => array(
			'i18n' => true,
		),
		'content',
	);

	public static $behaviors = array(
		'Asgard\Behaviors\PageBehavior',
	);

	public function __toString() {
		return $this->title;
	}

	public static function configure($definition) {
		$definition->addProperty('another_property');
	}
}