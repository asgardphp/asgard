<?php
namespace Asgard\Files\Tests;

class SaveBehavior extends \Asgard\Entity\Behavior {
	public function call_save($entity) {
		$entity::trigger('save', array($entity));
	}

	public function call_destroy($entity) {
		$entity::trigger('destroy', array($entity));
	}
}