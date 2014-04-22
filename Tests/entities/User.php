<?php
namespace Asgard\Form\Tests\Entities;

class User extends \Asgard\Core\Entity {
	public static $properties = array(
		'name' => array(
			'required'
		)
	);

	public static $behaviors = array(
		'Asgard\Orm\OrmBehavior'
	);

	public static $relations = array(
		'comments' => array(
			'entity' => 'Asgard\Form\Tests\Entities\Comment',
			'has' => 'many'
		)
	);

	public function __toString() {
		return $this->name;
	}
}