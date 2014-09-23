<?php
namespace Asgard\Entityform\Tests\Entities;

class Comment extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'content' => [
				'required'
			],
			'user' => [
				'type' => 'entity',
				'entity' => 'Asgard\Entityform\Tests\Entities\User',
			]
		];
	}

	public function __toString() {
		return $this->content;
	}
}