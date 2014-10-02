<?php
namespace Asgard\Entity;

/**
 * Behavior to handle relations.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface RelationsBehavior {
	/**
	 * Article::relations()
	 * @return array
	 */
	public function static_relations();

	/**
	 * Article::relation('parent')
	 * @param  string $name
	 * @return mixed
	 */
	public function static_relation($name);

	/**
	 * Article::hasRelation('parent')
	 * @param  string $name
	 * @return boolean
	 */
	public function static_hasRelation($name);

	/**
	 * $article->relation('category')
	 * @param  AsgardEntityEntity $entity
	 * @param  string             $relation
	 * @return mixed
	 */
	public function call_related(\Asgard\Entity\Entity $entity, $relation);
}