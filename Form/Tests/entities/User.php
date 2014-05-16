<?php
namespace Asgard\Form\Tests\Entities;

class User extends \Asgard\Entity\Entity {
	public static function definition(\Asgard\Entity\EntityDefinition $definition) {
		$definition->properties = array(
			'name' => array(
				'required'
			)
		);

		$definition->behaviors = array(
			new \Asgard\Orm\OrmBehavior
		);

		$definition->relations = array(
			'comments' => array(
				'entity' => 'Asgard\Form\Tests\Entities\Comment',
				'has' => 'many'
			)
		);
	}

	public function __toString() {
		return $this->name;
	}
}