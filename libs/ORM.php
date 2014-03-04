<?php
namespace Asgard\ORM\Libs;

class ORM {
	protected $entity;
	protected $with;
	protected $where = array();
	protected $orderBy;
	protected $limit;
	protected $offset;
	protected $join = array();
	protected $page;
	protected $per_page;

	protected $tmp_dal = null;
		
	function __construct($entity) {
		$this->entity = $entity;

		$this->orderBy($entity::getDefinition()->meta['order_by']);
	}

	public function __call($relationName, $args) {
		$current_entity = $this->entity;
		if(!$current_entity::getDefinition()->hasRelation($relationName))
			throw new \Exception('Relation '.$relationName.' does not exist.');
		$relation = $current_entity::getDefinition()->relations[$relationName];
		$reverse_relation = $relation->reverse();
		$reverse_relation_name = $reverse_relation['name'];
		$relation_entity = $relation['entity'];

		$newOrm = $relation_entity::orm();
		$newOrm->where($this->where);

		$newOrm->join(array($reverse_relation_name => $this->join));

		return $newOrm;
	}

	public function __get($name) {
		return $this->$name()->get();
	}

	public function joinToEntity($relation, $entity) {
		if(!$relation instanceof EntityRelation) {
			$current_entity = $this->entity;
			$relation = $current_entity::getDefinition()->relations[$relation];
		}

		if($relation['polymorphic']) {
			$this->where(array($relation['link_type'] => $entity->getEntityName()));
			$relation['real_entity'] = $entity->getEntityName();
		}
		$this->join($relation);

		$this->where(array($relation->name.'.id' => $entity->id));

		return $this;
	}

	public function join($relations) {
		$this->join[] = $relations;
		return $this;
	}

	public function getTable() {
		$current_entity = $this->entity;
		return $current_entity::getTable();
	}

	public function geti18nTable() {
		return $this->getTable().'_translation';
	}

	public function toEntity($raw, $entityClass=null) {
		if(!$entityClass)
			$entityClass = $this->entity;
		$new = new $entityClass;
		return ORMHandler::unserializeSet($new, $raw);
	}

	public function next() {
		if(!$this->tmp_dal)
			$this->tmp_dal = $this->getDAL();
		if(!($r = $this->tmp_dal->next()))
			return false;
		else
			return $this->toEntity($r);
	}

	public function ids() {
		return $this->values('id');
	}

	public function values($attr) {
		$res = array();
		foreach($this->get() as $one)
			$res[] = $one->$attr;
		return $res;
	}
	
	public function first() {
		$this->limit(1);
		
		$res = $this->get();
		if(!sizeof($res))
			return false;
		return $res[0];
	}
	
	public function all() {
		return static::get();
	}

	public function getDAL() {
		$current_entity = $this->entity;
		$dal = new \Asgard\DB\DAL(\Asgard\Core\App::get('db'));
		$table = $this->getTable();
		$dal->orderBy($this->orderBy);
		$dal->limit($this->limit);
		$dal->offset($this->offset);
		$dal->groupBy($table.'.id');

		$dal->where($this->processConditions($this->where));

		if($current_entity::isI18N()) {
			$translation_table = $this->geti18nTable();
			$selects = array($table.'.*');
			foreach($current_entity::getDefinition()->properties() as $name=>$property) {
				if($property->i18n)
					$selects[] = $translation_table.'.'.$name;
			}
			$dal->select($selects);
			$dal->from($table);
			$dal->leftjoin(array(
				$translation_table => $this->processConditions(array(
					$table.'.id = '.$translation_table.'.id',
					$translation_table.'.locale' => \Asgard\Core\App::get('config')->get('locale')
				))
			));
		}
		else {
			$dal->from($table);
		}

		$table = $current_entity::getTable();
		$this->recursiveJointures($dal, $this->join, $current_entity, $table);

		return $dal;
	}

	public function recursiveJointures($dal, $jointures, $current_entity, $table) {
		foreach($jointures as $k=>$relation) {
			if(is_array($relation)) {
				$relationName = \Asgard\Utils\Tools::array_get(array_keys($relation), 0);
				$recJoins = \Asgard\Utils\Tools::array_get(array_values($relation), 0);
				$relation = $current_entity::getDefinition()->relations[$relationName];
				$entity = $relation['entity'];

				$this->jointure($dal, $relation, $current_entity, $table);
				$this->recursiveJointures($dal, $recJoins, $entity, $relation->name);
			}
			else {
				if(!$relation instanceof EntityRelation)
					$relation = $current_entity::getDefinition()->relations[$relation];
				$this->jointure($dal, $relation, $current_entity, $table);
			}
		}
	}

	public function jointure($dal, $relation, $current_entity, $ref_table) {
		if($relation['polymorphic'])
			$relation_entity = $relation['real_entity'];
		else
			$relation_entity = $relation['entity'];
		$relationName = $relation->name;

		switch($relation['type']) {
			case 'hasOne':
			case 'belongsTo':
				$link = $relation['link'];
				$table = $relation_entity::getTable();
				$dal->rightjoin(array(
					$table.' '.$relationName => $this->processConditions(array(
						$ref_table.'.'.$link.' = '.$relationName.'.id'
					))
				));
				break;
			case 'hasMany':
				$link = $relation['link'];
				$table = $relation_entity::getTable();
				$dal->rightjoin(array(
					$table.' '.$relationName => $this->processConditions(array(
						$ref_table.'.id'.' = '.$relationName.'.'.$link
					))
				));
				break;
			case 'HMABT':
				$dal->rightjoin(array(
					$relation['join_table'] => $this->processConditions(array(
						$relation['join_table'].'.'.$relation['link_a'].' = '.$ref_table.'.id',
					))
				));
				$dal->rightjoin(array(
					$relation_entity::getTable().' '.$relationName => $this->processConditions(array(
						$relation['join_table'].'.'.$relation['link_b'].' = '.$relationName.'.id',
					))
				));
				break;
		}

		if($relation_entity::isI18N()) {
			$table = $relation_entity::getTable();
			$translation_table = $table.'_translation';
			$dal->leftjoin(array(
				$translation_table.' '.$relationName.'_translation' => $this->processConditions(array(
					$table.'.id = '.$relationName.'_translation.id',
					$relationName.'_translation.locale' => \Asgard\Core\App::get('config')->get('locale')
				))
			));
		}
	}
	
	public function get() {
		$entities = array();
		$ids = array();
		$current_entity = $this->entity;

		$dal = $this->getDAL();

		$rows = $dal->get();
		foreach($rows as $row) {
			if(!$row['id'])
				continue;
			$entities[] = $this->toEntity($row);
			$ids[] = $row['id'];
		}

		if(sizeof($entities) && sizeof($this->with)) {
			foreach($this->with as $relation_name=>$closure) {
				$rel = $current_entity::getDefinition()->relations[$relation_name];
				$relation_type = $rel['type'];
				$relation_entity = $rel['entity'];

				switch($relation_type) {
					case 'hasOne':
					case 'belongsTo':
						$link = $rel['link'];
						
						$orm = $relation_entity::where(array('id IN ('.implode(', ', $ids).')'));
						if(is_callable($closure))
							$closure($orm);
						$res = $orm->get();
						foreach($entities as $entity) {
							$id = $entity->$link;
							$filter = array_filter($res, function($result) use ($id) {
								return ($id == $result->id);
							});
							$filter = array_values($filter);
							if(isset($filter[0]))
								$entity->$relation_name = $filter[0];
							else
								$entity->$relation_name = null;
						}
						break;
					case 'hasMany':
						$link = $rel['link'];
						
						$orm = $relation_entity::where(array($link.' IN ('.implode(', ', $ids).')'));
						if(is_callable($closure))
							$closure($orm);
						$res = $orm->get();
						foreach($entities as $entity) {
							$id = $entity->id;
							$filter = array_filter($res, function($result) use ($id, $link) {
								return ($id == $result->$link);
							});
							$filter = array_values($filter);
							$entity->$relation_name = $filter;
						}
						break;
					case 'HMABT':
						$join_table = $rel['join_table'];
						$currentEntity_idfield = $rel['link_a'];
						$relationEntity_idfield = $rel['link_b'];

						$reverse_relation = $rel->reverse();
						$reverse_relation_name = $reverse_relation['name'];

						$orm = $relation_entity::join($reverse_relation_name)
							->where(array(
								$current_entity::getTable().'.id IN ('.implode(', ', $ids).')',
							));

						if(is_callable($closure))
							$closure($orm);
						$res = $orm->getDAL()->addSelect($join_table.'.'.$currentEntity_idfield.' as __ormrelid')->groupBy(null)->get();
						foreach($entities as $entity) {
							$id = $entity->id;
							$filter = array_filter($res, function($result) use ($id) {
								return $id == $result['__ormrelid'];
							});
							$filter = array_values($filter);
							$mres = array();
							foreach($filter as $m)
								$mres[] = $this->toEntity($m, $relation_entity);
							$entity->$relation_name = $mres;
						}
						break;
					default:
						throw new \Exception('Relation type '.$relation_type.' does not exist');
				}
			}
		}
		
		return $entities;
	}
	
	public function selectQuery($sql, $args=array()) {
		$entities = array();
		$entity = $this->entity;
		
		$dal = new \Asgard\DB\DAL(\Asgard\Core\App::get('db'));
		$rows = $dal->query($sql, $args)->all();
		foreach($rows as $row)
			$entities[] = ORMHandler::unserializeSet(new $entity, $row);
			
		return $entities;
	}
	
	public function paginate($page, $per_page=10) {
		$page = $page ? $page:1;
		$this->offset(($page-1)*$per_page);
		$this->limit($per_page);

		$this->page = $page;
		$this->per_page = $per_page;
		
		return $this;
	}

	public function getPaginator() {
		if($this->page === null || $this->per_page === null)
			return;
		return new \Asgard\Utils\Paginator($this->count(), $this->page, $this->per_page);
	}
	
	public function with($with, $closure=null) {
		$this->with[$with] = $closure;
		
		return $this;
	}

	protected function replaceTable($sql) {
		$entity = $this->entity;
		$table = $this->getTable();
		$i18nTable = $this->geti18nTable();
		preg_match_all('/(?<![\.a-z0-9-_`\(\)])([a-z0-9-_]+)(?![\.a-z0-9-_`\(\)])/', $sql, $matches);
		foreach($matches[0] as $property) {
			if(!$entity::hasProperty($property))
				continue;
			$table = $entity::property($property)->i18n ? $i18nTable:$table;
			// $sql = preg_replace('/(?<![\.a-z0-9-_`\(\)])('.$property.')(?![\.a-z0-9-_`\(\)])/', $table.'.`$1`', $sql);
			$sql = preg_replace('/(?<![\.a-z0-9-_`\(\)])('.$property.')(?![\.a-z0-9-_`\(\)])/', $table.'.$1', $sql);
		}

		return $sql;
	}

	protected function processConditions($conditions) {
		foreach($conditions as $k=>$v) {
			if(!is_array($v)) {
				$newK = $this->replaceTable($k);
				$conditions[$newK] = $this->replaceTable($v);
				if($newK != $k)
					unset($conditions[$k]);
			}
			else {
				$conditions[$k] = $this->processConditions($conditions[$k]);
			}
		}

		return $conditions;
	}
	
	public function where($conditions, $val=null) {
		if(is_array($conditions))
			$this->where[] = $this->processConditions($conditions);
		else
			$this->where[] = $this->processConditions(array($conditions=>$val));
		
		return $this;
	}
	
	public function offset($offset) {
		$this->offset = $offset;
		return $this;
	}
	
	public function limit($limit) {
		$this->limit = $limit;
		return $this;
	}
	
	public function orderBy($orderBy) {
		$this->orderBy = $orderBy;
		return $this;
	}
	
	public function delete() {
		$count = 0;
		while($entity = $this->next())
			$count += $entity->destroy();

		return $count;
	}
	
	public function update($values) {
		while($entity = $this->next())
			$entity->save($values);
		return $this;
	}
	
	public function count($group_by=null) {
		return $this->getDAL()->count($group_by);
	}
	
	public function min($what, $group_by=null) {
		return $this->getDAL()->min($what, $group_by);
	}
	
	public function max($what, $group_by=null) {
		return $this->getDAL()->max($what, $group_by);
	}
	
	public function avg($what, $group_by=null) {
		return $this->getDAL()->avg($what, $group_by);
	}
	
	public function sum($what, $group_by=null) {
		return $this->getDAL()->sum($what, $group_by);
	}
	
	public function reset() {
		$this->where = array();
		$this->with = array();
		$this->orderBy = null;
		$this->limit = null;
		$this->offset = null;
		$this->join = array();
		
		return $this;
	}
}
