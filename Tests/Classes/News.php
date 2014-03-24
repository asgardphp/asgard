<?php
namespace Asgard\Core\Tests\Classes;

class News extends \Asgard\Core\Entity {
	public static $properties = array(
		'title' => array(
			'validation' => array(
				'required' => true,
			)
		),
		'content',
		'published' => 'date'
	);

	public function __toString() {
		return $this->title;
	}

	public static function configure($definition) {
		$definition->addProperty('another_property');
	}
}