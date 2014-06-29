<?php
namespace Asgard\Http\Tests\Fixtures\Controllers;

class Entity extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'title',
			'content'
		];
	}
}