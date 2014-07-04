<?php
namespace Asgard\Entityform\Tests\Entities;

class FakeEntity {
	public $id;
	public $comment;

	public function __construct($id, $comment) {
		$this->id = $id;
		$this->comment = $comment;
	}

	public function __toString() {
		return $this->comment;
	}
}

class FakeORM {
	protected $i=0;
	protected $entities;

	public function __construct() {
		$this->entities = [
			new FakeEntity(1, 'Nice!'),
			new FakeEntity(2, 'Great!'),
			new FakeEntity(3, 'Terrible..'),
		];
	}

	public function next() {
		if(!isset($this->entities[$this->i]))
			return;
		return $this->entities[$this->i++];
	}
}

class PersistenceRelationsBehavior extends \Asgard\Entity\Behavior implements \Asgard\Entity\PersistenceBehavior, \Asgard\Entity\RelationsBehavior {
	#Article::loadBy('title', 'hello world')
	public function static_loadBy($property, $value) {
	}

	#Static methods
	#Article::relations()
	public function static_relations() {
	}

	#Article::relation('parent')
	public function static_relation($name) {
		return ['entity'=>'Asgard\Entityform\Tests\Entities\Comment', 'has'=>'many'];
	}

	#Article::hasRelation('parent')
	public function static_hasRelation($name) {
	}

	#Article::load(2)
	public function static_load($id) {
	}

	#Article::orm()
	public function static_orm() {
		return new FakeORM;
	}

	#Article::destroyAll()
	public function static_destroyAll() {
	}

	#Article::destroyOne()
	public function static_destroyOne($id) {
	}

	#Article::create()
	public function static_create(array $values=[], $force=false) {
	}

	#Methods
	#$article->save()
	public function call_save(\Asgard\Entity\Entity $entity, array $values=null, $force=false) {
	}

	#$article->destroy()
	public function call_destroy(\Asgard\Entity\Entity $entity) {
	}

	#$article->isNew()
	public function call_isNew(\Asgard\Entity\Entity $entity) {
	}

	#$article->isOld()
	public function call_isOld(\Asgard\Entity\Entity $entity) {
	}

	#$article->relation('category')
	public function call_relation(\Asgard\Entity\Entity $entity, $relation) {
	}
}