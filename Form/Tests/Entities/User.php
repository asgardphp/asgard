<?php
namespace Asgard\Form\Tests\Entities;

class User extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'name' => [
				'required'
			]
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];

		$definition->relations = [
			'comments' => [
				'entity' => 'Asgard\Form\Tests\Entities\Comment',
				'has' => 'many'
			]
		];
	}

	public function __toString() {
		return $this->name;
	}
}