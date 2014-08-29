<?php
namespace Asgard\Orm;

/**
 * ORM for related entities.
 */
class CollectionORM extends ORM implements \Asgard\Entity\Collection {
	/**
	 * Parent entity
	 * @var \Asgard\Entity\Entity
	 */
	protected $parent;
	/**
	 * Relation name
	 * @var string
	 */
	protected $relation;

	/**
	 * Constructor.
	 * @param \Asgar\dEntity\Entity $entity   \Asgard\Entity\Entity
	 * @param string                          $relation_name
	 * @param string                          $locale        default locale
	 * @param string                          $prefix        tables prefix
	 * @param DataMapper                      $datamapper
	 * @param \Asgard\Container\Factory       $paginatorFactory
	 */
	public function __construct(\Asgard\Entity\Entity $entity, $relation_name, $locale=null, $prefix=null, DataMapper $datamapper=null, \Asgard\Container\Factory $paginatorFactory=null) {
		$this->parent = $entity;

		$this->relation = $entity->getDefinition()->relations[$relation_name];

		parent::__construct($this->relation['entity'], $locale, $prefix, $datamapper, $paginatorFactory);

		$this->joinToEntity($this->relation->reverse(), $entity);
	}
	
	/**
	 * Update the related entities.
	 * @param  array           $ids  array of entity ids
	 * @return CollectionORM         $this
	 */
	public function sync($ids) {
		if(!$ids)
			$ids = [];
		if(!is_array($ids))
			$ids = [$ids];
		foreach($ids as $k=>$v) {
			if($v instanceof \Asgard\Entity\Entity)
				$ids[$k] = (int)$v->id;
		}
	
		switch($this->relation->type()) {
			case 'hasMany':
				$relation_entity = $this->relation['entity'];
				$link = $this->relation->getLink();
				$dal = new \Asgard\Db\DAL($relation_entity::getTable());
				$dal->where([$link => $this->parent->id])->update([$link => 0]);
				if($ids)
					$dal->reset()->where(['id IN ('.implode(', ', $ids).')'])->update([$link => $this->parent->id]);
				break;
			case 'HMABT':
				$dal = new \Asgard\Db\DAL($this->relation->getTable());
				$dal->where([$this->relation->getLinkA() => $this->parent->id])->delete();
				$dal->reset();
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
	 * @param  array  $ids
	 */
	public function add($ids) {
		if(!is_array($ids))
			$ids = [$ids];
		foreach($ids as $k=>$id)
			if($id instanceof \Asgard\Entity\Entity)
				$ids[$k] = (int)$id->id;
			
		switch($this->relation['type']) {
			case 'hasMany':
				$relation_entity = $this->relation['entity'];
				$dal = new \Asgard\Db\DAL($relation_entity::getTable());
				foreach($ids as $id)
					$dal->reset()->where(['id' => $id])->update([$this->relation->getLink() => $this->parent->id]);
				break;
			case 'HMABT':
				$dal = new \Asgard\Db\DAL($this->relation['join_table']);
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
		$relEntity = $this->relation['entity'];
		$new = new $relEntity;
		switch($this->relation['type']) {
			case 'hasMany':
				$params[$this->relation->getLink()] = $this->parent->id;
				$new->save($params);
				break;
			case 'HMABT':
				$new->save($params);
				$this->add($new->id);
				break;
		}
		return $new;
	}
	
	/**
	 * Remove entities from the relation.
	 * @param  array $id
	 * @return CollectionORM $this
	 */
	public function remove($ids) {
		if(!is_array($ids))
			$ids = [$ids];
		foreach($ids as $k=>$id) {
			if($id instanceof \Asgard\Entity\Entity)
				$ids[$k] = $id->id;
		}
			
		switch($this->relation['type']) {
			case 'hasMany':
				$relation_entity = $this->relation['entity'];
				$dal = new \Asgard\Db\DAL($relation_entity::getTable());
				foreach($ids as $id)
					$dal->reset()->where(['id' => $id])->update([$this->relation->getLink() => 0]);
				break;
			case 'HMABT':
				$dal = new \Asgard\Db\DAL($this->relation->getTable());
				foreach($ids as $id)
					$dal->reset()->where([$this->relation->getLinkA() => $this->parent->id, $this->relation->getLinkB() => $id])->delete();
				break;
			default:
				throw new \Exception('Collection only works with hasMany and HMABT');
		}
		
		return $this;
	}
}
