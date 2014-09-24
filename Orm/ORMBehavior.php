<?php
namespace Asgard\Orm;

/**
 * ORM Behavior.
 */
class ORMBehavior extends \Asgard\Entity\Behavior implements \Asgard\Entity\PersistenceBehavior, \Asgard\Entity\RelationsBehavior {
	/**
	 * DataMapper dependency.
	 * @var DataMapper
	 */
	protected $dataMapper;
	/**
	 * Entity class.
	 * @var string
	 */
	protected $entityClass;

	/**
	 * Behavior loading.
	 * @param  \Asgard\Entity\EntityDefinition $definition
	 */
	public function load(\Asgard\Entity\EntityDefinition $definition) {
		$this->entityClass = $definition->getClass();

		if(!isset($definition->order_by))
			$definition->set('order_by', 'id DESC');
		
		$definition->hook('get', [$this, 'hookGet']);
		$definition->hook('getTranslations', [$this, 'hookgetTranslations']);
		$definition->hook('validation', [$this, 'hookValidation']);
	}

	/**
	 * Return the datamapper.
	 * @return DataMapper
	 */
	protected function getDataMapper() {
		if(!$this->dataMapper)
			$this->dataMapper = $this->definition->getContainer()['dataMapper'];
		return $this->dataMapper;
	}

	/**
	 * Hook for the entity get method.
	 * @param  \Asgard\Hook\HookChain $chain
	 * @param  \Asgard\Entity\Entity  $entity
	 * @param  string                 $name
	 */
	public function hookGet(\Asgard\Hook\HookChain $chain, \Asgard\Entity\Entity $entity, $name) {
		if($this->getDataMapper()->hasRelation($this->definition, $name))
			return $entity->{$name} = $this->getDataMapper()->getRelated($entity, $name);
	}

	/**
	 * Hook for the entity translations get method.
	 * @param  \Asgard\Hook\HookChain $chain
	 * @param  \Asgard\Entity\Entity  $entity
	 * @param  string                 $name
	 * @param  string                 $locale
	 */
	public function hookgetTranslations(\Asgard\Hook\HookChain $chain, \Asgard\Entity\Entity $entity, $name, $locale) {
		return $this->getDataMapper()->getTranslations($entity, $locale);
	}

	/**
	 * Hook for the entity validation.
	 * @param  \Asgard\Hook\HookChain       $chain
	 * @param  \Asgard\Entity\Entity        $entity
	 * @param  AsgardValidationValidator    $validator
	 * @param  array                        $data
	 */
	public function hookValidation(\Asgard\Hook\HookChain $chain, \Asgard\Entity\Entity $entity, \Asgard\Validation\Validator $validator, array &$data) {
		$this->getDataMapper()->prepareValidator($entity, $validator);
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
		#$article->authors()
		if($this->getDataMapper()->hasRelation($this->definition, $name)) {
			$processed = true;
			return $entity->related($name);
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
	 * Article::relations()
	 * @return array
	 */
	public function static_relations() {
		return $this->getDataMapper()->relations($this->definition);
	}

	/**
	 * Article::relation('parent')
	 * @param  string $name relation name
	 * @return array  relation parameters
	 */
	public function static_relation($name) {
		return $this->getDataMapper()->relation($this->definition, $name);
	}

	/**
	 * Article::hasRelation('parent')
	 * @param  string $name relation name
	 * @return boolean true if the entity class has the relation, false otherwise
	 */
	public function static_hasRelation($name) {
		return $this->getDataMapper()->hasRelation($this->definition, $name);
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
	 * $article->related('category')
	 * @param  \Asgard\Entity\Entity $entity
	 * @param  string             $relation   relation name
	 * @return \Asgrd\Entity\Entity|CollectionORM
	 */
	public function call_related(\Asgard\Entity\Entity $entity, $relation) {
		return $this->getDataMapper()->related($entity, $relation);
	}
}