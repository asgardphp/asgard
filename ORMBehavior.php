<?php
namespace Asgard\Orm;

class ORMBehavior implements \Asgard\Core\Behavior {
	public static function load($entityDefinition, $params=null) {
		$entityName = $entityDefinition->getClass();
		$ormHandler = new \Asgard\Orm\Libs\ORMHandler($entityDefinition);

		#Static methods
		#Article::getTable()
		$entityDefinition->addStaticMethod('getTable', function() use($entityName) {
			return \Asgard\Orm\Libs\ORMHandler::getTable($entityName);
			// $em->getTable($entityClass)
		});
		#Article::orm()
		$entityDefinition->addStaticMethod('orm', function() use($ormHandler) {
			return $ormHandler->getORM();
		});
		#Article::load(2)
		$entityDefinition->addStaticMethod('load', function($id) use($ormHandler) {
			return $ormHandler->load($id);
		});
		#Article::destroyAll()
		$entityDefinition->addStaticMethod('destroyAll', function() use($ormHandler) {
			return $ormHandler->destroyAll();
		});
		#Article::destroyOne()
		$entityDefinition->addStaticMethod('destroyOne', function($id) use($ormHandler) {
			return $ormHandler->destroyOne($id);
		});
		#Article::hasRelation('parent')
		$entityDefinition->addStaticMethod('hasRelation', function($name) use($entityDefinition) {
			return array_key_exists($name, $entityDefinition->relations());
		});

		#Methods
		#$article->isNew()
		$entityDefinition->addMethod('isNew', function($entity) use($ormHandler) {
			return $ormHandler->isNew($entity);
			// $em->isNew($entity)
		});
		#$article->isOld()
		$entityDefinition->addMethod('isOld', function($entity) use($ormHandler) {
			return $ormHandler->isOld($entity);
		});
		#Relations
		$entityDefinition->addMethod('relation', function($entity, $relation) use($ormHandler) {
			return $ormHandler->relation($entity, $relation);
		});
		#Relation properties
		$entityDefinition->addMethod('getRelationProperty', function($entity, $relation) use($ormHandler) {
			return $ormHandler->getRelationProperty($entity, $relation);
		});

		#Hooks
		$entityDefinition->hookOn('callStatic', function($chain, $name, $args) use($ormHandler) {
			$res = null;
			if(strpos($name, 'loadBy') === 0) {
				$chain->found = true;
				preg_match('/^loadBy(.*)/', $name, $matches);
				$property = strtolower($matches[1]);
				$val = $args[0];
				return $ormHandler->getORM()->where(array($property => $val))->first();
			}
			#Article::where() / ::limit() / ::orderBy() / ..
			elseif(method_exists('Asgard\Orm\Libs\ORM', $name)) {
				$chain->found = true;
				return call_user_func_array(array($ormHandler->getORM(), $name), $args);
			}
		});

		// $em->getRelation($entity, $relationName, )
		$entityDefinition->hookOn('call', function($chain, $entity, $name, $args) use($ormHandler) {
			$res = null;
			if($entity::hasRelation($name)) {
				$chain->found = true;
				$res = $entity->relation($name);
			}
			return $res;
		});

		// $em->load($entityClass, $id)
		$entityDefinition->hookOn('construct', function($chain, $entity, $id) use($ormHandler) {
			$ormHandler->construct($chain, $entity, $id);
		});

		// $em->destroy($entity) // avec un hook/event "destroy"
		#$article->destroy()
		$entityDefinition->hookOn('destroy', function($chain, $entity) use($ormHandler) {
			$ormHandler->destroy($entity);
		});

		// $em->save($entity) // avec un hook/event "save"
		#$article->save()
		$entityDefinition->hookOn('save', function($chain, $entity) use($ormHandler) {
			$ormHandler->save($entity);
		});
		
		// $em->getI18N($entity, $attribute, $lang) // sans behavior utiliser getI18N, avec, le hook s'integre a get()
		#$article->title
		$entityDefinition->hookAfter('get', function($chain, $entity, $name, $lang) {
			return \Asgard\Orm\Libs\ORMHandler::fetch($entity, $name, $lang);
		});

		// $em->relation($entity, $name)
		$entityDefinition->hookBefore('get', function($chain, $entity, $name, $lang) {
			if($entity::hasRelation($name)) {
				$rel = $entity->relation($name);
				if($rel instanceof \Asgard\Core\Collection)
					return $rel->get();
				else
					return $rel;
			}
		});
	}
}