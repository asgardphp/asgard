<?php
namespace Asgard\Entity;

interface PersistenceBehavior {
	#Article::orm()
	public function static_orm();

	#Article::loadBy('title', 'hello world')
	public function static_loadBy($property, $value);

	#Article::load(2)
	public function static_load($id);

	#Article::destroyAll()
	public function static_destroyAll();

	#Article::destroyOne()
	public function static_destroyOne($id);

	#Article::create()
	public function static_create(array $values=[], $force=false);

	#$article->save()
	public function call_save(\Asgard\Entity\Entity $entity, array $values=null, $force=false);

	#$article->destroy()
	public function call_destroy(\Asgard\Entity\Entity $entity);

	#$article->isNew()
	public function call_isNew(\Asgard\Entity\Entity $entity);

	#$article->isOld()
	public function call_isOld(\Asgard\Entity\Entity $entity);
}