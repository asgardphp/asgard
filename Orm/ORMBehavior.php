<?php
namespace Asgard\Orm;

/**
 * ORM Behavior.
 */
class ORMBehavior extends \Asgard\Entity\Behavior implements \Asgard\Entity\PersistenceBehavior, \Asgard\Entity\RelationsBehavior {
	protected $dataMapper;
	protected $entityClass;

	/**
	 * Behavior loading.
	 * @param  \Asgard\Entity\EntityDefinition $definition
	 */
	public function load(\Asgard\Entity\EntityDefinition $definition) {
		$this->entityClass = $definition->getClass();

		if(!isset($definition->order_by))
			$definition->set('order_by', 'id DESC');
		
		$definition->addProperty('id', [
			'type'     => 'text', 
			'editable' => false, 
			'required' => false,
			'position' => 0,
			'defaut'   => 0,
			'orm'      => [
				'type'              => 'int(11)',
				'auto_increment'    => true,
				'key'               => 'PRI',
				'nullable'          => false,
			],
		]);	

		foreach($definition->relations as $name=>$params)
			$definition->relations[$name] = new EntityRelation($definition, $name, $params);
		
		$definition->hook('set', [$this, 'hookSet']);
		$definition->hook('get', [$this, 'hookGet']);
		$definition->hook('getI18N', [$this, 'hookgetI18N']);
		$definition->hook('validation', [$this, 'hookValidation']);
	}

	/**
	 * Return the datamapper.
	 * @return DataMapper
	 */
	protected function getDataMapper() {
		if(!$this->dataMapper)
			$this->dataMapper = $this->definition->getContainer()->make('dataMapper');
		return $this->dataMapper;
	}

	/**
	 * Hook for the entity set method.
	 * @param  \Asgard\Hook\HookChain $chain
	 * @param  \Asgard\Entity\Entity  $entity
	 * @param  string                 $name
	 * @param  mixed                  $value
	 */
	public function hookSet(\Asgard\Hook\HookChain $chain, \Asgard\Entity\Entity $entity, $name, $value) {
		if($entity::hasRelation($name)) {
			$rel = $this->static_getRelationProperty($name);
			if($rel->type() == 'belongsTo')
				$entity->{$rel->getLink()} = $value;
		}
	}

	/**
	 * Hook for the entity get method.
	 * @param  \Asgard\Hook\HookChain $chain
	 * @param  \Asgard\Entity\Entity  $entity
	 * @param  string                 $name
	 */
	public function hookGet(\Asgard\Hook\HookChain $chain, \Asgard\Entity\Entity $entity, $name) {
		if($entity::hasRelation($name)) {
			$rel = $this->dataMapper->relation($entity, $name);
			if($rel instanceof \Asgard\Entity\Collection)
				return $rel->get();
			else
				return $rel->first();
		}
	}

	/**
	 * Hook for the entity i18n get method.
	 * @param  \Asgard\Hook\HookChain $chain
	 * @param  \Asgard\Entity\Entity  $entity
	 * @param  string                 $name
	 * @param  string                 $locale
	 */
	public function hookgetI18N(\Asgard\Hook\HookChain $chain, \Asgard\Entity\Entity $entity, $name, $locale) {
		return $this->getDataMapper()->getI18N($entity, $locale);
	}

	/**
	 * Hook for the entity validation.
	 * @param  \Asgard\Hook\HookChain       $chain
	 * @param  \Asgard\Entity\Entity        $entity
	 * @param  AsgardValidationValidator    $validator
	 * @param  array                        $data
	 */
	public function hookValidation(\Asgard\Hook\HookChain $chain, \Asgard\Entity\Entity $entity, \Asgard\Validation\Validator $validator, array &$data) {
		foreach($this->definition->relations as $name=>$relation) {
			$data[$name] = $this->getDataMapper()->relation($entity, $name);
			$validator->attribute($name, $relation->getRules());
		}
	}

	/**
	 * Catch all static calls.
	 * @param  string  $name      static call name
	 * @param  array   $args
	 * @param  boolean $processed
	 * @return null|\Asgard\Orm\ORM
	 */
	public function staticCatchAll($name, array $args, &$processed) {
		#Article::where() / ::limit() / ::orderBy() / ..
		if(method_exists('Asgard\Orm\ORM', $name)) {
			$processed = true;
			return call_user_func_array([$this->getDataMapper()->orm($this->entityClass), $name], $args);
		}
	}

	/**
	 * Catch all calls.
	 * @param  \Asgard\Entity\Entity $entity
	 * @param  string                $name      static call name
	 * @param  array                 $args
	 * @param  boolean               $processed
	 * @return null|\Asgard\Entity\Entity|\Asgard\Orm\CollectionORM
	 */
	public function callCatchAll($entity, $name, $args, &$processed) {
		if($entity::hasRelation($name)) {
			$processed = true;
			return $entity->relation($name);
		}
	}

	#Static calls

	/**
	 * Article::loadBy('title', 'hello world')
	 * @param  string $property property name
	 * @param  mixed  $value
	 * @return null|\Asgard\Entity\Entity
	 */
	public function static_loadBy($property, $value) {
		return $this->getDataMapper()->orm($this->entityClass)->where([$property => $value])->first();
	}

	/**
	 * Article::getRelationProperty('category')
	 * @param  string $relation relation name
	 * @return EntityRelation
	 */
	public function static_getRelationProperty($relation) {
		return $this->getDataMapper()->getRelation($this->definition, $relation);
	}
	
	/**
	 * Article::relations()
	 * @return array
	 */
	public function static_relations() {
		return $this->definition->relations;
	}

	/**
	 * Article::relation('parent')
	 * @param  string $name relation name
	 * @return array  relation parameters
	 */
	public function static_relation($name) {
		return $this->static_relations()[$name];
	}

	/**
	 * Article::hasRelation('parent')
	 * @param  string $name relation name
	 * @return boolean true if the entity class has the relation, false otherwise
	 */
	public function static_hasRelation($name) {
		return array_key_exists($name, $this->static_relations());
	}

	/**
	 * Article::load(2)
	 * @param  integer $id entity id
	 * @return null|\Asgard\Entity\Entity
	 */
	public function static_load($id) {
		return $this->getDataMapper()->load($this->entityClass, $id);
	}

	/**
	 * Article::getTable()
	 * @return string
	 */
	public function static_getTable() {
		return $this->getDataMapper()->getTable($this->entityClass);
	}

	/**
	 * Article::orm()
	 * @return \Asgard\Orm\ORM
	 */
	public function static_orm() {
		return $this->getDataMapper()->orm($this->entityClass);
	}

	/**
	 * Article::destroyAll()
	 * @return integer number of destroyed entities
	 */
	public function static_destroyAll() {
		return $this->getDataMapper()->destroyAll($this->entityClass);
	}

	/**
	 * Article::destroyOne()
	 * @param  integer $id entity id
	 * @return boolean true if entity was destroyed, false otherwise
	 */
	public function static_destroyOne($id) {
		return $this->getDataMapper()->destroyOne($this->entityClass, $id);
	}

	/**
	 * Article::create()
	 * @param  array  $values  default attributes
	 * @param  boolean $force  skip validation
	 * @return \Asgard\Entity\Entity
	 */
	public function static_create(array $values=[], $force=false) {
		return $this->getDataMapper()->create($this->entityClass, $values, $force);
	}

	#Calls
	
	/**
	 * $article->save()
	 * @param  \Asgard\Entity\Entity $entity
	 * @param  array             $values default attributes
	 * @param  boolean            $force  skip validation
	 * @return boolean  true fo successful saving, false otherwise
	 */
	public function call_save(\Asgard\Entity\Entity $entity, array $values=null, $force=false) {
		return $this->getDataMapper()->save($entity, $values, $force);
	}

	/**
	 * $article->destroy()
	 * @param  \Asgard\Entity\Entity $entity
	 * @return boolean true if entity was destroyed, false otherwise
	 */
	public function call_destroy(\Asgard\Entity\Entity $entity) {
		return $this->getDataMapper()->destroy($entity);
	}

	/**
	 * $article->isNew()
	 * @param  \Asgard\Entity\Entity $entity
	 * @return boolean true if entity is not stored yet
	 */
	public function call_isNew(\Asgard\Entity\Entity $entity) {
		return $this->getDataMapper()->isNew($entity);
	}

	/**
	 * $article->isOld()
	 * @param  \Asgard\Entity\Entity $entity
	 * @return boolean true if entity is already stored
	 */
	public function call_isOld(\Asgard\Entity\Entity $entity) {
		return $this->getDataMapper()->isOld($entity);
	}

	/**
	 * $article->relation('category')
	 * @param  \Asgard\Entity\Entity $entity
	 * @param  string             $relation   relation name
	 * @return \Asgrd\Entity\Entity|CollectionORM
	 */
	public function call_relation(\Asgard\Entity\Entity $entity, $relation) {
		return $this->getDataMapper()->relation($entity, $relation);
	}
}