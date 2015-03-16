<?php
namespace Asgard\Orm;

/**
 * ORM for related entities.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class PolymorphicCollectionORM implements CollectionORMInterface {
	/**
	 * Parent entity.
	 * @var \Asgard\Entity\Entity
	 */
	protected $parent;
	/**
	 * Relation instance.
	 * @var EntityRelation
	 */
	protected $relation;
	/**
	 * DataMapper dependency.
	 * @var DataMapperInterface
	 */
	protected $dataMapper;
	/**
	 * Paginator factory dependency.
	 * @var \Asgard\Common\PaginatorFactoryInterface
	 */
	protected $paginatorFactory;
	/**
	 * Locale.
	 * @var string
	 */
	protected $locale;
	/**
	 * Tables prefix.
	 * @var string
	 */
	protected $prefix;

	/**
	 * Constructor.
	 * @param \Asgard\Entity\Entity $entity            $entity
	 * @param string                                   $relationName
	 * @param DataMapperInterface                      $dataMapper
	 * @param string                                   $locale        default locale
	 * @param string                                   $prefix        tables prefix
	 * @param \Asgard\Common\PaginatorFactoryInterface $paginatorFactory
	 */
	public function __construct(\Asgard\Entity\Entity $entity, $relationName, DataMapperInterface $dataMapper, $locale=null, $prefix=null, \Asgard\Common\PaginatorFactoryInterface $paginatorFactory=null) {
		$this->parent = $entity;
		$this->relation = $dataMapper->relation($entity->getDefinition(), $relationName);
		$this->dataMapper = $dataMapper;
		$this->locale = $locale;
		$this->prefix = $prefix;
		$this->paginatorFactory = $paginatorFactory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sync($entities, $force=false) {
		if(!is_array($entities))
			$entities = [$entities];
		$perclass = [];

		foreach($entities as $entity)
			$perclass[get_class($entity)][] = $entity;

		foreach($perclass as $class=>$entities) {
			$targetDefinition = $this->parent->getDefinition()->getEntityManager()->get($class);
			$cORM = new CollectionORM($this->parent, $this->relation->getName(), $this->dataMapper, $this->locale, $this->prefix, $this->paginatorFactory, $targetDefinition);
			$cORM->sync($entities);
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function add($ids) {
		throw new \Exception('Not implemented'); 
	}

	/**
	 * {@inheritDoc}
	 */
	public function create(array $params=[]) {
		throw new \Exception('Not implemented'); 
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove($ids) {
		throw new \Exception('Not implemented');
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear() { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function __call($relationName, array $args) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function __get($name) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function joinToEntity($relation, \Asgard\Entity\Entity $entity) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function join($relation, array $subrelations=null) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function getTable() { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function getTranslationTable() { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function next() { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function ids() { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function values($property) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function first() {
		$classes = $this->relation->get('entities');

		foreach($classes as $class) {
			$targetDefinition = $this->parent->getDefinition()->getEntityManager()->get($class);
			$cORM = new CollectionORM($this->parent, $this->relation->getName(), $this->dataMapper, $this->locale, $this->prefix, $this->paginatorFactory, $targetDefinition);
			if($entity = $cORM->first())
				return $entity;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function all() { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function getDAL() { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function get() {
		$entities = [];
		$classes = $this->relation->get('entities');

		foreach($classes as $class) {
			$targetDefinition = $this->parent->getDefinition()->getEntityManager()->get($class);
			$cORM = new CollectionORM($this->parent, $this->relation->getName(), $this->dataMapper, $this->locale, $this->prefix, $this->paginatorFactory, $targetDefinition);
			$entities = array_merge($entities, $cORM->get());
		}

		return $entities;
	}

	/**
	 * {@inheritDoc}
	 */
	public function selectQuery($sql, array $args=[]) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function paginate($page=1, $per_page=10) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function getPaginator() { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function with($with, \Closure $closure=null) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function where($conditions, $val=null) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function offset($offset) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function limit($limit) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function orderBy($orderBy) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function delete() { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function update(array $values) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function count($group_by=null) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function min($what, $group_by=null) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function max($what, $group_by=null) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function avg($what, $group_by=null) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function sum($what, $group_by=null) { throw new \Exception('Not implemented'); }

	/**
	 * {@inheritDoc}
	 */
	public function reset() { throw new \Exception('Not implemented'); }
}