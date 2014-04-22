<?php
namespace Asgard\Form\Tests\Entities;

class Comment extends \Asgard\Core\Entity {
	public static $properties = array(
		'content' => array(
			'required'
		)
	);

	public static $behaviors = array(
		'Asgard\Orm\OrmBehavior'
	);

	public static $relations = array(
		'user' => array(
			'entity' => 'Asgard\Form\Tests\Entities\User',
			'has' => 'one'
		)
	);

	public function __toString() {
		return $this->content;
	}
}