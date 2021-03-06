<?php
namespace Asgard\Orm;

/**
 * ORM Behavior.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ORMBehavior extends \Asgard\Entity\Behavior implements \Asgard\Entity\PersistenceBehavior, \Asgard\Entity\RelationsBehavior {
	/**
	 * DataMapper dependency.
	 * @var DataMapperInterface
	 */
	protected $dataMapper;
	/**
	 * Entity class.
	 * @var string
	 */
	protected $entityClass;

	/**
	 * Behavior loading.
	 * @param  \Asgard\Entity\Definition $definition
	 */
	public function load(\Asgard\Entity\Definition $definition) {
		$this->entityClass = $definition->getClass();

		if(!$definition->has('order_by'))
			$definition->set('order_by', 'id DESC');

		$definition->hook('get', [$this, 'hookGet']);
		$definition->hook('getTranslations', [$this, 'hookgetTranslations']);
		$definition->hook('validation', [$this, 'hookValidation']);
	}

	/**
	 * Return the datamapper.
	 * @return DataMapperInterface
	 */
	protected function getDataMapper() {
		if(!$this->dataMapper)
			$this->dataMapper = $this->definition->getContainer()['dataMapper'];
		return $this->dataMapper;
	}

	/**
	 * Hook for the entity get method.
	 * @param \Asgard\Hook\Chain    $chain
	 * @param \Asgard\Entity\Entity $entity
	 * @param string                $name
	 */
	public function hookGet(\Asgard\Hook\Chain $chain, \Asgard\Entity\Entity $entity, $name) {
		$name = strtolower($name);
		if($this->getDataMapper()->hasRelation($this->definition, $name)) {
			if($entity->data['properties'][$name] === null) {
				$entity->set($name, $this->getDataMapper()->getRelated($entity, $name));
				return $entity->data['properties'][$name];
			}
		}
	}

	/**
	 * Hook for the entity translations get method.
	 * @param  \Asgard\Hook\Chain $chain
	 * @param  \Asgard\Entity\Entity  $entity
	 * @param  string                 $name
	 * @param  string                 $locale
	 */
	public function hookgetTranslations(\Asgard\Hook\Chain $chain, \Asgard\Entity\Entity $entity, $name, $locale) {
		return $this->getDataMapper()->getTranslations($entity, $locale);
	}

	/**
	 * Hook for the entity validation.
	 * @param  \Asgard\Hook\Chain                    $chain
	 * @param  \Asgard\Entity\Entity                 $entity
	 * @param  \Asgard\Validation\ValidatorInterface $validator
	 */
	public function hookValidation(\Asgard\Hook\Chain $chain, \Asgard\Entity\Entity $entity, \Asgard\Validation\ValidatorInterface $validator) {
		$this->getDataMapper()->prepareValidator($entity, $validator);
	}

	/**
	 * Catch all static calls.
	 * @param  string  $name      static call name
	 * @param  array   $args
	 * @param  boolean $processed
	 * @return \Asgard\Orm\ORM
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
	 * @return \Asgard\Entity\Entity|CollectionORMInterface
	 */
	public function callCatchAll($entity, $name, $args, &$processed) {
		#$article->authors()
		if($this->getDataMapper()->hasRelation($this->definition, $name)) {
			$processed = true;
			return $this->getDataMapper()->related($entity, $name);
		}
	}

	#Static calls

	/**
	 * Article::loadBy('title', 'hello world')
	 * @param  string $property property name
	 * @param  mixed  $value
	 * @return \Asgard\Entity\Entity
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
	 * @param  string $name   relation name
	 * @return EntityRelation
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
	 * @return \Asgard\Entity\Entity
	 */
	public function static_load($id) {
		return $this->getDataMapper()->load($this->entityClass, $id);
	}

	/**
	 * Article::getTable()
	 * @return string
	 */
	public function static_getTable() {
		return $this->getDataMapper()->getTable($this->definition);
	}

	/**
	 * Article::orm()
	 * @return \Asgard\Orm\ORMInterface
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
	 * @param  array      $values  default attributes
	 * @param  array|null $groups  validation groups
	 * @return \Asgard\Entity\Entity
	 */
	public function static_create(array $values=[], $groups=[]) {
		return $this->getDataMapper()->create($this->entityClass, $values, $groups);
	}

	#Calls

	/**
	 * $article->save()
	 * @param  \Asgard\Entity\Entity $entity
	 * @param  array                 $values default attributes
	 * @param  array|null            $groups validation groups
	 * @return \Asgard\Entity\Entity
	 */
	public function call_save(\Asgard\Entity\Entity $entity, array $values=[], array $groups=[]) {
		return $this->getDataMapper()->save($entity, $values, $groups);
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
	 * $article->related('category')
	 * @param  \Asgard\Entity\Entity $entity
	 * @param  string             $relation   relation name
	 * @return \Asgrd\Entity\Entity|CollectionORMInterface
	 */
	public function call_related(\Asgard\Entity\Entity $entity, $relation) {
		return $this->getDataMapper()->related($entity, $relation);
	}
}