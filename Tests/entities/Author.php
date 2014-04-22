<?php
namespace Asgard\Orm\Tests\Entities;

class Author extends \Asgard\Core\Entity {
	public static $properties = array(
		'name',
	);

	public static $behaviors = array(
		'Asgard\Orm\ORMBehavior'
	);

	public static $relations = array(
		'news' => array(
			'entity' => 'Asgard\Orm\Tests\Entities\News',
			'has' => 'many'
		),
	);
}