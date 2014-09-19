<?php
namespace Asgard\Entity;

/**
 * 
 */
interface PersistenceBehavior {
	#Article::orm()
	/**
	 * [static_orm description]
	 * @return [type]
	 */
	public function static_orm();

	#Article::loadBy('title', 'hello world')
	/**
	 * [static_loadBy description]
	 * @param  [type] $property
	 * @param  [type] $value
	 * @return [type]
	 */
	public function static_loadBy($property, $value);

	#Article::load(2)
	/**
	 * [static_load description]
	 * @param  [type] $id
	 * @return [type]
	 */
	public function static_load($id);

	#Article::destroyAll()
	/**
	 * [static_destroyAll description]
	 * @return [type]
	 */
	public function static_destroyAll();

	#Article::destroyOne()
	/**
	 * [static_destroyOne description]
	 * @param  [type] $id
	 * @return [type]
	 */
	public function static_destroyOne($id);

	#Article::create()
	/**
	 * [static_create description]
	 * @param  [type]  $values
	 * @param  boolean $force
	 * @return [type]
	 */
	public function static_create(array $values=[], $force=false);

	#$article->save()
	/**
	 * [call_save description]
	 * @param  AsgardEntityEntity $entity
	 * @param  [type]             $values
	 * @param  boolean            $force
	 * @return [type]
	 */
	public function call_save(\Asgard\Entity\Entity $entity, array $values=null, $force=false);

	#$article->destroy()
	/**
	 * [call_destroy description]
	 * @param  AsgardEntityEntity $entity
	 * @return [type]
	 */
	public function call_destroy(\Asgard\Entity\Entity $entity);

	#$article->isNew()
	/**
	 * [call_isNew description]
	 * @param  AsgardEntityEntity $entity
	 * @return [type]
	 */
	public function call_isNew(\Asgard\Entity\Entity $entity);

	#$article->isOld()
	/**
	 * [call_isOld description]
	 * @param  AsgardEntityEntity $entity
	 * @return [type]
	 */
	public function call_isOld(\Asgard\Entity\Entity $entity);
}