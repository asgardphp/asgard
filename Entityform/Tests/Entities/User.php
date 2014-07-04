<?php
namespace Asgard\Entityform\Tests\Entities;

class User extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'name' => [
				'required'
			]
		];

		$definition->behaviors = [
			new PersistenceRelationsBehavior
		];

		$definition->relations = [
			'comments' => [
				'entity' => 'Asgard\Entityform\Tests\Entities\Comment',
				'has' => 'many'
			]
		];
	}

	public function __toString() {
		return $this->name;
	}
}