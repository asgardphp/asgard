<?php
namespace Asgard\Files\Tests\Entities;

class News extends \Asgard\Core\Entity {
	public static $properties = array(
		'title',
		'image' => array(
			'required' => true,
			'type' => 'file',
			'filetype' => 'image',
			'validation' => array(
				'image',
				'extension' => array('jpg', 'gif', 'png'),
			)
		),
	);
}