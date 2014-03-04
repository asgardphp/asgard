<?php
namespace Asgard\ORM\Tests\Entities;

class News extends \Asgard\Core\Entity {
	public static $properties = array(
		'title',
		'content',
	);

	public static $relations = array(
		'category' => array(
			'entity' => 'Asgard\ORM\Tests\Entities\Category',
			'has' => 'one'
		),
		'author' => array(
			'entity' => 'Asgard\ORM\Tests\Entities\Author',
			'has' => 'one'
		),
	);
}