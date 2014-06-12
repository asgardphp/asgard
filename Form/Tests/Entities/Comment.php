<?php
namespace Asgard\Form\Tests\Entities;

class Comment extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = [
			'content' => [
				'required'
			]
		];

		$definition->behaviors = [
			new \Asgard\Orm\ORMBehavior
		];

		$definition->relations = [
			'user' => [
				'entity' => 'Asgard\Form\Tests\Entities\User',
				'has' => 'one'
			]
		];
	}

	public function __toString() {
		return $this->content;
	}
}