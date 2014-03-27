<?php
namespace Asgard\Orm\Libs;

class CollectionORM extends ORM implements \Asgard\Core\Collection {
	protected $parent;
	protected $relation;

	public function __construct($entity, $relation_name) {
		$this->parent = $entity;

		$this->relation = $entity->getDefinition()->relations[$relation_name];

		parent::__construct($this->relation['entity']);

		$this->joinToEntity($this->relation->reverse(), $entity);
	}
	
	public function sync($ids) {
		if(!$ids)
			$ids = array();
		if(!is_array($ids))
			$ids = array($ids);
		foreach($ids as $k=>$v)
			if($v instanceof \Asgard\Core\Entity)
				$ids[$k] = (int)$v->id;
	
		switch($this->relation['type']) {
			case 'hasMany':
				$relation_entity = $this->relation['entity'];
				$link = $this->relation['link'];
				$dal = new DAL($relation_entity::getTable());
				$dal->where(array($link => $this->parent->id))->getDAL()->update(array($link => 0));
				if($ids)
					$dal->reset()->where(array('id IN ('.implode(', ', $ids).')'))->getDAL()->update(array($link => $this->parent->id));
				break;
			case 'HMABT':
				$dal = new DAL($this->relation['join_table']);
				$dal->where(array($this->relation['link_a'] => $this->parent->id))->delete();
				$dal->reset();
				$i = 1;
				foreach($ids as $id) {
					if(isset($this->relation['sortfield']) && $this->relation['sortfield'])
						$dal->insert(array($this->relation['link_a'] => $this->parent->id, $this->relation['link_b'] => $id, $this->relation['sortfield'] => $i++));
					else
						$dal->insert(array($this->relation['link_a'] => $this->parent->id, $this->relation['link_b'] => $id));
				}
				break;
			default:
				throw new \Exception('Collection only works with hasMany and HMABT');
		}
		
		return $this;
	}
	
	public function add($ids) {
		if(!is_array($ids))
			$ids = array($ids);
		foreach($ids as $k=>$id)
			if($id instanceof \Asgard\Core\Entity)
				$ids[$k] = (int)$id->id;
			
		switch($this->relation['type']) {
			case 'hasMany':
				$relation_entity = $this->relation['entity'];
				$dal = new DAL($relation_entity::getTable());
				foreach($ids as $id)
					$dal->reset()->where(array('id' => $id))->getDAL()->update(array($this->relation['link'] => $this->parent->id));
				break;
			case 'HMABT':
				$dal = new DAL($this->relation['join_table']);
				$i = 1;
				foreach($ids as $id) {
					$dal->reset()->where(array($this->relation['link_a'] => $this->parent->id, $this->relation['link_b'] => $id))->delete();
					if(isset($this->relation['sortfield']) && $this->relation['sortfield'])
						$dal->insert(array($this->relation['link_a'] => $this->parent->id, $this->relation['link_b'] => $id, $this->sortfield => $i++));
					else
						$dal->insert(array($this->relation['link_a'] => $this->parent->id, $this->relation['link_b'] => $id));
				}
				break;
			default:
				throw new \Exception('Collection only works with hasMany and HMABT');
		}
		
		return $this;
	}

	public function create($params=array()) {
		$relEntity = $this->relation['entity'];
		$new = new $relEntity;
		switch($this->relation['type']) {
			case 'hasMany':
				$params[$this->relation['link']] = $this->parent->id;
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
			$ids = array($ids);
		foreach($ids as $k=>$id)
			if($id instanceof \Asgard\Core\Entity)
				$ids[$k] = $id->id;
			
		switch($this->relation['type']) {
			case 'hasMany':
				$relation_entity = $this->relation['entity'];
				$dal = new DAL($relation_entity::getTable());
				foreach($ids as $id)
					$dal->reset()->where(array('id' => $id))->getDAL()->update(array($this->relation['link'] => 0));
				break;
			case 'HMABT':
				$dal = new DAL($this->relation['join_table']);
				foreach($ids as $id)
					$dal->reset()->where(array($this->relation['link_a'] => $this->parent->id, $this->relation['link_b'] => $id))->delete();
				break;
			default:
				throw new \Exception('Collection only works with hasMany and HMABT');
		}
		
		return $this;
	}
}
