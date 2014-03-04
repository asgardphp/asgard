<?php
namespace Asgard\ORM\Tests\Entities;

class Category extends \Asgard\Core\Entity {
	public static $properties = array(
		'title',
		'description',
	);

	public static $relations = array(
		'news' => array(
			'entity' => 'Asgard\ORM\Tests\Entities\News',
			'has' => 'many'
		),
	);
}