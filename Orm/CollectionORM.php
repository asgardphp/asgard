<?php
namespace Asgard\Orm;

class CollectionORM extends ORM implements \Asgard\Entity\Collection {
	protected $parent;
	protected $relation;

	public function __construct(\Asgard\Entity\Entity $entity, $relation_name, $db, $locale=null, $prefix=null, $app=null) {
		$this->parent = $entity;

		$this->relation = $entity->getDefinition()->relations[$relation_name];

		parent::__construct($this->relation['entity'], $db, $locale, $prefix, $app);

		$this->joinToEntity($this->relation->reverse(), $entity);
	}
	
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
				$dal->where([$link => $this->parent->id])->getDAL()->update([$link => 0]);
				if($ids)
					$dal->reset()->where(['id IN ('.implode(', ', $ids).')'])->getDAL()->update([$link => $this->parent->id]);
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
					$dal->reset()->where(['id' => $id])->getDAL()->update([$this->relation->getLink() => $this->parent->id]);
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
					$dal->reset()->where(['id' => $id])->getDAL()->update([$this->relation->getLink() => 0]);
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
