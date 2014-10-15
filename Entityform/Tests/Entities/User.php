<?php
namespace Asgard\Entityform\Tests\Entities;

class User extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\Definition $definition) {
		$definition->properties = [
			'name' => [
				'required'
			],
			'comments' => [
				'type' => 'entity',
				'entity' => 'Asgard\Entityform\Tests\Entities\Comment',
				'many' => true,
			]
		];
	}

	public function __toString() {
		return $this->name;
	}
}