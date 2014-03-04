<?php
namespace Coxis\Files\Tests\Entities;

class News extends \Coxis\Core\Entity {
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