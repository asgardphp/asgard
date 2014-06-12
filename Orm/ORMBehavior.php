<?php
namespace Asgard\Orm;

class ORMBehavior extends \Asgard\Entity\Behavior {
	protected $dataMapper;
	protected $entityClass;

	public function load(\Asgard\Entity\EntityDefinition $definition) {
		$this->entityClass = $entityClass = $definition->getClass();

		if(!isset($definition->order_by))
			$definition->order_by = 'id DESC';
		
		$definition->addProperty('id', [
			'type'     => 'text', 
			'editable' => false, 
			'required' => false,
			'position' => 0,
			'defaut'   => 0,
			'orm'      => [
				'type'              => 'int(11)',
				'auto_increment'	=> true,
				'key'	            => 'PRI',
				'nullable'	        => false,
			],
		]);	

		foreach($definition->relations as $name=>$params)
			$definition->relations[$name] = new EntityRelation($definition, $name, $params);
		
		$definition->hook('get', [$this, 'hookGet']);
		$definition->hook('getI18N', [$this, 'hookgetI18N']);
		$definition->hook('validation', [$this, 'hookValidation']);
	}

	protected function getDataMapper() {
		if(!$this->dataMapper) {
			$app = $this->definition->getApp();
			$this->dataMapper = new DataMapper(
				$app['db'],
				$app['config']->get('locale'),
				$app['config']->get('database/prefix'),
				$app
			);
		}
		return $this->dataMapper;
	}

	public function hookGet(\Asgard\Hook\HookChain $chain, \Asgard\Entity\Entity $entity, $name, $lang) {
		if($entity::hasRelation($name)) {
			$rel = $this->dataMapper->relation($entity, $name);
			if($rel instanceof \Asgard\Entity\Collection)
				return $rel->get();
			else
				return $rel->first();
		}
	}

	public function hookgetI18N(\Asgard\Hook\HookChain $chain, \Asgard\Entity\Entity $entity, $name, $lang) {
		return $this->getDataMapper()->getI18N($entity, $lang);
	}

	public function hookValidation(\Asgard\Hook\HookChain $chain, \Asgard\Entity\Entity $entity, \Asgard\Validation\Validator $validator, array &$data) {
		foreach($this->definition->relations() as $name=>$relation) {
			$data[$name] = $this->dataMapper->relation($entity, $name);
			$validator->attribute($name, $relation->getRules());
		}
	}

	public function staticCatchAll($name, array $args, &$processed) {
		#Article::where() / ::limit() / ::orderBy() / ..
		if(method_exists('Asgard\Orm\ORM', $name)) {
			$processed = true;
			return call_user_func_array([$this->getDataMapper()->orm($this->entityClass), $name], $args);
		}
	}

	public function callCatchAll($entity, $name, $args, &$processed) {
		if($entity::hasRelation($name)) {
			$processed = true;
			return $entity->relation($name);
		}
	}

	#Article::loadBy('title', 'hello world')
	public function static_loadBy($property, $value) {
		return $this->getDataMapper()->orm($this->entityClass)->where([$property => $value])->first();
	}

	#Article::getRelationProperty('category')
	public function static_getRelationProperty($relation) {
		return $this->getDataMapper()->getRelationProperty($this->entityClass, $relation);
	}

	#Static methods
	#Article::relations()
	public function static_relations() {
		return $this->definition->relations;
	}

	#Article::relation('parent')
	public function static_relation($name) {
		return $this->static_relations()[$name];
	}

	#Article::hasRelation('parent')
	public function static_hasRelation($name) {
		return array_key_exists($name, $this->static_relations());
	}

	#Article::load(2)
	public function static_load($id) {
		return $this->getDataMapper()->load($this->entityClass, $id);
	}

	public function static_getTable() {
		return $this->getDataMapper()->getTable($this->entityClass);
	}

	#Article::orm()
	public function static_orm() {
		return $this->getDataMapper()->orm($this->entityClass);
	}

	#Article::destroyAll()
	public function static_destroyAll() {
		return $this->getDataMapper()->destroyAll($this->entityClass);
	}

	#Article::destroyOne()
	public function static_destroyOne($id) {
		return $this->getDataMapper()->destroyOne($this->entityClass, $id);
	}

	#Article::create()
	public function static_create(array $values=[], $force=false) {
		return $this->getDataMapper()->create($this->entityClass, $values, $force);
	}

	#Methods
	#$article->save()
	public function call_save(\Asgard\Entity\Entity $entity, array $values=null, $force=false) {
		return $this->getDataMapper()->save($entity, $values, $force);
	}

	#$article->destroy()
	public function call_destroy(\Asgard\Entity\Entity $entity) {
		return $this->getDataMapper()->destroy($entity);
	}

	#$article->isNew()
	public function call_isNew(\Asgard\Entity\Entity $entity) {
		return $this->getDataMapper()->isNew($entity);
	}

	#$article->isOld()
	public function call_isOld(\Asgard\Entity\Entity $entity) {
		return $this->getDataMapper()->isOld($entity);
	}

	#$article->relation('category')
	public function call_relation(\Asgard\Entity\Entity $entity, $relation) {
		return $this->getDataMapper()->relation($entity, $relation);
	}
}