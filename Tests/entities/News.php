<?php
namespace Asgard\Orm\Tests\Entities;

class News extends \Asgard\Core\Entity {
	public static $properties = array(
		'title',
		'content',
	);

	public static $behaviors = array(
		'Asgard\Orm\ORMBehavior'
	);

	public static $relations = array(
		'category' => array(
			'entity' => 'Asgard\Orm\Tests\Entities\Category',
			'has' => 'one'
		),
		'author' => array(
			'entity' => 'Asgard\Orm\Tests\Entities\Author',
			'has' => 'one'
		),
	);
}