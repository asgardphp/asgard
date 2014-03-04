<?php
namespace Asgard\ORM\Tests\Entities;

class Author extends \Asgard\Core\Entity {
	public static $properties = array(
		'name',
	);

	public static $relations = array(
		'news' => array(
			'entity' => 'Asgard\ORM\Tests\Entities\News',
			'has' => 'many'
		),
	);
}