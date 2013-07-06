<?php
namespace Coxis\ORM\Libs;

class CollectionORM extends ORM implements \Coxis\Core\Collection {
	protected $parent;
	protected $relation;

	function __construct($model, $relation_name) {
		$this->parent = $model;

		$this->relation = $model->getDefinition()->relations[$relation_name];

		parent::__construct($this->relation['model']);

		$this->joinToModel($this->relation->reverse(), $model);
	}
	
	public function sync($ids) {
		if(!$ids)
			$ids = array();
		if(!is_array($ids))
			$ids = array($ids);
		foreach($ids as $k=>$v)
			if($v instanceof \Coxis\Core\Model)
				$ids[$k] = (int)$v->id;
	
		switch($this->relation['type']) {
			case 'hasMany':
				$relation_model = $this->relation['model'];
				$link = $this->relation['link'];
				$dal = new DAL($relation_model::getTable());
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
			if($id instanceof \Coxis\Core\Model)
				$ids[$k] = (int)$id->id;
			
		switch($this->relation['type']) {
			case 'hasMany':
				$relation_model = $this->relation['model'];
				$dal = new DAL($relation_model::getTable());
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
		$relModel = $this->relation['model'];
		$new = new $relModel;
		switch($this->relation['type']) {
			case 'hasMany':
				$params[$this->relation['link']] = $this->parent->id;
				break;
			case 'HMABT':
				d('todo');#todo
				break;
		}
		$new->save($params);
		return $new;
	}
	
	public function remove($ids) {
		if(!is_array($ids))
			$ids = array($ids);
		foreach($ids as $k=>$id)
			if($id instanceof \Coxis\Core\Model)
				$ids[$k] = $id->id;
			
		switch($this->relation['type']) {
			case 'hasMany':
				$relation_model = $this->relation['model'];
				$dal = new DAL($relation_model::getTable());
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
