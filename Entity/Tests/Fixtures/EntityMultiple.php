<?php
namespace Asgard\Entity\Tests\Fixtures;

class EntityMultiple extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
			'names' => array(
				'type' => 'text',
				'multiple' => true
			),
		);
	}
}