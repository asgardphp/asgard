<?php
namespace Asgard\Entity\Tests\Fixtures;

class EntityWithFile extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
			'name',
			'files' => array(
				'type' => 'file',
				'multiple' => true
			),
			'file' => 'file',
		);
	}
}