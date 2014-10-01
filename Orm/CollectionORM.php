<?php
namespace Asgard\Orm;

/**
 * ORM for related entities.
 */
class CollectionORM extends ORM implements \Asgard\Entity\Collection {
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
	 * Constructor.
	 * @param \Asgar\dEntity\Entity $entity   \Asgard\Entity\Entity
	 * @param string                          $relation_name
	 * @param DataMapperInterface                      $datamapper
	 * @param string                          $locale        default locale
	 * @param string                          $prefix        tables prefix
	 * @param \Asgard\Container\Factory       $paginatorFactory
	 */
	public function __construct(\Asgard\Entity\Entity $entity, $relationName, DataMapperInterface $dataMapper, $locale=null, $prefix=null, \Asgard\Container\Factory $paginatorFactory=null) {
		$this->parent = $entity;

		$this->relation = $dataMapper->relation($entity->getDefinition(), $relationName);

		parent::__construct($this->relation->getTargetDefinition(), $dataMapper, $locale, $prefix, $paginatorFactory);

		$this->joinToEntity($this->relation->reverse(), $entity);
	}
	
	/**
	 * Update the related entities.
	 * @param integer|array           $ids   array of entity ids
	 * @param boolean                 $force true to skip validation
	 * @return CollectionORMInterface        $this
	 */
	public function sync($ids, $force=false) {
		if(!$ids)
			$ids = [];
		if(!is_array($ids))
			$ids = [$ids];
		foreach($ids as $k=>$v) {
			if($v instanceof \Asgard\Entity\Entity) {
				if($v->isNew())
					$this->dataMapper->save($v, null, $force);
				$ids[$k] = (int)$v->id;
			}
		}
	
		switch($this->relation->type()) {
			case 'hasMany':
			$relationEntityDefinition = $this->relation->getTargetDefinition();
				$link = $this->relation->getLink();
				$dal = new \Asgard\Db\DAL($this->dataMapper->getDB(), $this->dataMapper->getTable($relationEntityDefinition));
				$dal->where([$link => $this->parent->id])->update([$link => 0]);
				if($ids) {
					$newDal = new \Asgard\Db\DAL($this->dataMapper->getDB(), $this->dataMapper->getTable($relationEntityDefinition));
					$newDal->where(['id IN ('.implode(', ', $ids).')'])->update([$link => $this->parent->id]);
				}
				break;
			case 'HMABT':
				$dal = new \Asgard\Db\DAL($this->dataMapper->getDB(), $this->relation->getTable());
				$dal->where([$this->relation->getLinkA() => $this->parent->id])->delete();
				$dal = new \Asgard\Db\DAL($this->dataMapper->getDB(), $this->relation->getTable());
				$i = 1;
				foreach($ids as $id) {
					if(isset($this->relation['sortfield']) && $this->relation['sortfield'])
						$dal->insert([$this->relation->getLinkA() => $this->parent->id, $this->relation->getLinkB() => $id, $this->relation['sortfield'] => $i++]);
					else
						$dal->insert([$this->relation->getLinkA() => $this->parent->id, $this->relation->getLinkB() => $id]);
				}
				break;
			default:
				throw new \Exception('Collection only works with hasMany and HMABT');
		}
		
		return $this;
	}
	
	/**
	 * Add new entities to the relation.
	 * @param integer|array $ids
	 */
	public function add($ids) {
		if(!is_array($ids))
			$ids = [$ids];
		foreach($ids as $k=>$id)
			if($id instanceof \Asgard\Entity\Entity)
				$ids[$k] = (int)$id->id;
			
		switch($this->relation['type']) {
			case 'hasMany':
				$relationEntityDefinition = $this->relation->getTargetDefinition();
				$dal = new \Asgard\Db\DAL($this->dataMapper->getDB(), $this->dataMapper->getTable($relationEntityDefinition));
				foreach($ids as $id)
					$dal->reset()->where(['id' => $id])->update([$this->relation->getLink() => $this->parent->id]);
				break;
			case 'HMABT':
				$dal = new \Asgard\Db\DAL($this->dataMapper->getDB(), $this->relation['join_table']);
				$i = 1;
				foreach($ids as $id) {
					$dal->reset()->where([$this->relation->getLinkA() => $this->parent->id, $this->relation->getLinkB() => $id])->delete();
					if(isset($this->relation['sortfield']) && $this->relation['sortfield'])
						$dal->insert([$this->relation->getLinkA() => $this->parent->id, $this->relation->getLinkB() => $id, $this->sortfield => $i++]);
					else
						$dal->insert([$this->relation->getLinkA() => $this->parent->id, $this->relation->getLinkB() => $id]);
				}
				break;
			default:
				throw new \Exception('Collection only works with hasMany and HMABT');
		}
		
		return $this;
	}

	/**
	 * Create a new entity and add it to the relation.
	 * @param  array $params entity default attributes
	 * @return \Asgard\Entity\Entitiy
	 */
	public function create(array $params=[]) {
		$new = $this->relation->getTargetDefinition()->make();
		switch($this->relation->type()) {
			case 'hasMany':
				$params[$this->relation->getLink()] = $this->parent->id;
				$this->dataMapper->save($new, $params);
				break;
			case 'HMABT':
				$this->dataMapper->save($new, $params);
				$this->add($new->id);
				break;
		}
		return $new;
	}
	
	/**
	 * Remove entities from the relation.
	 * @param  integer|array          $ids
	 * @return CollectionORMInterface $this
	 */
	public function remove($ids) {
		if(!is_array($ids))
			$ids = [$ids];
		foreach($ids as $k=>$id) {
			if($id instanceof \Asgard\Entity\Entity)
				$ids[$k] = $id->id;
		}
			
		switch($this->relation->type()) {
			case 'hasMany':
				$relation_entity = $this->relation['entity'];
				$dal = new \Asgard\Db\DAL($this->dataMapper->getDB(), $this->dataMapper->getTable($relation_entity));
				foreach($ids as $id)
					$dal->reset()->where(['id' => $id])->update([$this->relation->getLink() => 0]);
				break;
			case 'HMABT':
				$dal = new \Asgard\Db\DAL($this->dataMapper->getDB(), $this->relation->getTable());
				foreach($ids as $id)
					$dal->reset()->where([$this->relation->getLinkA() => $this->parent->id, $this->relation->getLinkB() => $id])->delete();
				break;
			default:
				throw new \Exception('Collection only works with hasMany and HMABT');
		}
		
		return $this;
	}
}
