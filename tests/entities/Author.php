<?php
namespace Coxis\ORM\Tests\Entities;

class Author extends \Coxis\Core\Entity {
	public static $properties = array(
		'name',
	);

	public static $relations = array(
		'news' => array(
			'entity' => 'Coxis\ORM\Tests\Entities\News',
			'has' => 'many'
		),
	);
}