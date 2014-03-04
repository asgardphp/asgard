<?php
namespace Coxis\ORM\Tests\Entities;

class Category extends \Coxis\Core\Entity {
	public static $properties = array(
		'title',
		'description',
	);

	public static $relations = array(
		'news' => array(
			'entity' => 'Coxis\ORM\Tests\Entities\News',
			'has' => 'many'
		),
	);
}