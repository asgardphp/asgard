<?php
namespace Asgard\Form\Tests\Entities;

class Comment extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
			'content' => array(
				'required'
			)
		);

		$definition->behaviors = array(
			new \Asgard\Orm\ORMBehavior
		);

		$definition->relations = array(
			'user' => array(
				'entity' => 'Asgard\Form\Tests\Entities\User',
				'has' => 'one'
			)
		);
	}

	public function __toString() {
		return $this->content;
	}
}