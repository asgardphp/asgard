<?php
namespace Asgard\Entity;

/**
 * Behavior to handle relations.
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
	 * @return [type]
	 */
	public function static_relation($name);

	/**
	 * Article::hasRelation('parent')
	 * @param  string $name
	 * @return [type]
	 */
	public function static_hasRelation($name);

	/**
	 * $article->relation('category')
	 * @param  AsgardEntityEntity $entity
	 * @param  [type]             $relation
	 * @return [type]
	 */
	public function call_relation(\Asgard\Entity\Entity $entity, $relation);
}