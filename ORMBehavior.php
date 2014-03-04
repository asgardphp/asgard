<?php
namespace Coxis\ORM;

class ORMBehavior implements \Coxis\Core\Behavior {
	public static function load($entityDefinition, $params=null) {
		$this->register('relation_required', function($attribute, $value, $params, $validator) {

			if(!$value->count()) {
				$msg = $validator->getMessage('email', $attribute, __('The relation ":attribute" is required.'));
				return Validation::format($msg, array(
					'attribute'	=>	__(str_replace('_', ' ', $attribute)),
				));
			}
		});

		$entityName = $entityDefinition->getClass();

		#Article::getTable()
		$entityDefinition->addStaticMethod('getTable', function() use($entityName) {
			return \Coxis\ORM\Libs\ORMHandler::getTable($entityName);
		});

		$ormHandler = new \Coxis\ORM\Libs\ORMHandler($entityDefinition);

		$entityDefinition->hookOn('constrains', function($chain, &$constrains) use($entityName) {
			foreach($entityName::getDefinition()->relations() as $name=>$relation) {
				$res = isset($this->params['validation']) ? $this->params['validation']:array();
				if(!is_array($res))
					$res = array('validation' => $res);
				if(isset($relation['required']) && $relation['required'])
					$constrains[$name]['relation_required'] = true;

				#e.g. todo
				// $constrains[] = array('relation_required', array(true, 'actualites'));
				// $constrains[] = array('relation_min', array(3, 'actualites'));
				// relation_required
				// true
				// actualites / relation
				// $entity
			}
		});

		// $entityDefinition->hookBefore('validation', function($chain, $entity, &$data, &$errors) {
		// 	$data = array_merge($data, $entity::getDefinition()->relations());
		// 	// foreach($entity::getDefinition()->relations() as $name=>$relation)
		// 	// 	$data[$name] = $entity->relation($name);
		// });

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
			elseif(method_exists('Coxis\ORM\Libs\ORM', $name)) {
				$chain->found = true;
				return call_user_func_array(array($ormHandler->getORM(), $name), $args);
			}
		});

		#$article->isNew()
		$entityDefinition->addMethod('isNew', function($entity) use($ormHandler) {
			return $ormHandler->isNew($entity);
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
		$entityDefinition->hookOn('call', function($chain, $entity, $name, $args) use($ormHandler) {
			$res = null;
			if($entity::hasRelation($name)) {
				$chain->found = true;
				$res = $entity->relation($name);
			}
			return $res;
		});

		$entityDefinition->hookOn('construct', function($chain, $entity, $id) use($ormHandler) {
			$ormHandler->construct($chain, $entity, $id);
		});

		#$article->destroy()
		$entityDefinition->hookOn('destroy', function($chain, $entity) use($ormHandler) {
			$ormHandler->destroy($entity);
		});

		#$article->save()
		$entityDefinition->hookOn('save', function($chain, $entity) use($ormHandler) {
			$ormHandler->save($entity);
		});
		
		#$article->title
		$entityDefinition->hookAfter('get', function($chain, $entity, $name, $lang) {
			return \Coxis\ORM\Libs\ORMHandler::fetch($entity, $name, $lang);
		});

		$entityDefinition->hookBefore('get', function($chain, $entity, $name, $lang) {
			if($entity::hasRelation($name)) {
				$rel = $entity->relation($name);
				if($rel instanceof \Coxis\Core\Collection)
					return $rel->get();
				else
					return $rel;
			}
		});
	}
}