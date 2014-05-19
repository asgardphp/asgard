<?php
namespace Asgard\Files\Tests\Entities;

class News extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
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

		$definition->behaviors = array(
			new \Asgard\Files\Tests\SaveBehavior,
			new \Asgard\Files\FilesBehavior
		);
	}
}