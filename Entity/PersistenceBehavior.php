<?php
namespace Asgard\Entity;

/**
 * Persistence behavior.
 */
interface PersistenceBehavior {
	/**
	 * Article::orm()
	 * @return mixed
	 */
	public function static_orm();

	/**
	 * Article::loadBy('title', 'hello world')
	 * @param  Property $property
	 * @param  mixed    $value
	 * @return Entity
	 */
	public function static_loadBy($property, $value);

	/**
	 * Article::load(2)
	 * @param  integer $id
	 * @return Entity
	 */
	public function static_load($id);

	/**
	 * Article::destroyAll()
	 * @return integer
	 */
	public function static_destroyAll();

	/**
	 * Article::destroyOne()
	 * @param  integer $id
	 * @return boolean
	 */
	public function static_destroyOne($id);

	/**
	 * Article::create()
	 * @param  array   $attrs
	 * @param  boolean $force
	 * @return Entity
	 */
	public function static_create(array $attrs=[], $force=false);

	/**
	 * $article->save()
	 * @param  Entity  $entity
	 * @param  array   $attrs
	 * @param  boolean $force
	 * @return Entity
	 */
	public function call_save(Entity $entity, array $attrs=null, $force=false);

	/**
	 * $article->destroy()
	 * @param  Entity $entity
	 */
	public function call_destroy(Entity $entity);
}