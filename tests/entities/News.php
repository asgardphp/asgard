<?php
namespace Coxis\ORM\Tests\Entities;

class News extends \Coxis\Core\Entity {
	public static $properties = array(
		'title',
		'content',
	);

	public static $relations = array(
		'category' => array(
			'entity' => 'Coxis\ORM\Tests\Entities\Category',
			'has' => 'one'
		),
		'author' => array(
			'entity' => 'Coxis\ORM\Tests\Entities\Author',
			'has' => 'one'
		),
	);
}