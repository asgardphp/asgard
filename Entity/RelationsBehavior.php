<?php
namespace Asgard\Entity;

interface RelationsBehavior {
	#Article::relations()
	public function static_relations();

	#Article::relation('parent')
	public function static_relation($name);

	#Article::hasRelation('parent')
	public function static_hasRelation($name);

	#$article->relation('category')
	public function call_relation(\Asgard\Entity\Entity $entity, $relation);
}