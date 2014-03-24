<?php
namespace Asgard\Files\Tests\Entities;

class News extends \Asgard\Core\Entity {
	public static $properties = array(
		'title',
		'image' => array(
			'type' => 'file',
			'filetype' => 'image',
			'validation' => array(
				'filerequired' => true,
				'image' => true,
				'allowed' => array('jpg', 'gif', 'png'),
			)
		),
	);
}