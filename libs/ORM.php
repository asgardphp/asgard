<?php
namespace Coxis\ORM\Libs;

class ORM {
	protected $model;
	protected $with;
	protected $where = array();
	protected $orderBy;
	protected $limit;
	protected $offset;
	protected $join = array();

	protected $tmp_dal = null;
		
	function __construct($model) {
		Profiler::checkpoint('ORM construct');
		$this->model = $model;

		#todo move it into orm/model
		$this->orderBy($model::getDefinition()->meta['order_by']);
	}

	public function __call($relationName, $args) {
		$current_model = $this->model;
		if(!$current_model::getDefinition()->hasRelation($relationName))
			throw new \Exception('Relation '.$relationName.' does not exist.');
		$relation = $current_model::getDefinition()->relations[$relationName];
		$reverse_relation = $relation->reverse();
		$reverse_relation_name = $reverse_relation['name'];
		$relation_model = $relation['model'];

		$newOrm = $relation_model::orm();
		$newOrm->where($this->where);

		$newOrm->join(array($reverse_relation_name => $this->join));

		return $newOrm;
	}

	public function __get($name) {
		return $this->$name()->get();
	}

	public function joinToModel($relation, $model) {
		if($relation['polymorphic']) {
			$this->where(array($relation['link_type'] => $model->getModelName()));
			$relation['real_model'] = $model->getModelName();
		}
		$this->join($relation);

		$this->where(array($relation->name.'.id' => $model->id));

		return $this;
	}

	public function join($relations) {
		$this->join[] = $relations;
		return $this;
	}

	public function getTable() {
		$current_model = $this->model;
		return $current_model::getTable();
	}

	public function geti18nTable() {
		return $this->getTable().'_translation';
	}

	public function toModel($raw, $modelClass=null) {
		if(!$modelClass)
			$modelClass = $this->model;
		$new = new $modelClass;
		return ORMHandler::unserializeSet($new, $raw);
	}

	public function next() {
		if(!$this->tmp_dal)
			$this->tmp_dal = $this->getDAL();
		if(!($r = $this->tmp_dal->next()))
			return false;
		else
			return $this->toModel($r);
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
		$current_model = $this->model;
		$dal = new DAL;
		$table = $this->getTable();
		$dal->orderBy($this->orderBy);
		$dal->limit($this->limit);
		$dal->offset($this->offset);
		$dal->groupBy($table.'.id');

		$dal->where($this->processConditions($this->where));

		if($current_model::isI18N()) {
			$translation_table = $this->geti18nTable();
			$selects = array($table.'.*');
			foreach($current_model::getDefinition()->properties() as $name=>$property)
				if($property->i18n)
					$selects[] = $translation_table.'.`'.$name.'`';
			$dal->select($selects);
			$dal->setTable($table);
			$dal->leftjoin(array(
				$translation_table => $this->processConditions(array(
					$table.'.id = '.$translation_table.'.id',
					$translation_table.'.locale' => \Config::get('locale')
				))
			));
		}
		else {
			$dal->select($table.'.*');
			$dal->setTable($table);
		}

		$table = $current_model::getTable();
		$this->recursiveJointures($dal, $this->join, $current_model, $table);

		return $dal;
	}

	public function recursiveJointures($dal, $jointures, $current_model, $table) {
		foreach($jointures as $k=>$v) {
			if(is_array($v)) {
				$relationName = get(array_keys($v), 0);
				$recJoins = get(array_values($v), 0);
				$relation = $current_model::getDefinition()->relations[$relationName];
				$model = $relation['model'];

				$this->jointure($dal, $relationName, $current_model, $table);
				$this->recursiveJointures($dal, $recJoins, $model, $table);
			}
			else {
				$relationName = $v;
				$this->jointure($dal, $relationName, $current_model, $table);
			}
		}
	}

	// todo replace alias $relationName by something else, in case of relations with the same name
	public function jointure($dal, $relation, $current_model, $ref_table) {
		if($relation['polymorphic'])
			$relation_model = $relation['real_model'];
		else
			$relation_model = $relation['model'];
		$relationName = $relation->name;

		switch($relation['type']) {
			case 'hasOne':
			case 'belongsTo':
				$link = $relation['link'];
				$table = $relation_model::getTable();
				$dal->rightjoin(array(
					$table.' '.$relationName => $this->processConditions(array(
						$ref_table.'.'.$link.' = '.$relationName.'.id'
					))
				));
				break;
			case 'hasMany':
				$link = $relation['link'];
				$table = $relation_model::getTable();
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
					$relation_model::getTable().' '.$relationName => $this->processConditions(array(
						$relation['join_table'].'.'.$relation['link_b'].' = '.$relationName.'.id',
					))
				));
				break;
		}

		if($relation_model::isI18N()) {
			$table = $relation_model::getTable();
			$translation_table = $table.'_translation';
			$dal->leftjoin(array(
				$translation_table.' '.$relationName.'_translation' => $this->processConditions(array(
					$table.'.id = '.$relationName.'_translation.id',
					$relationName.'_translation.locale' => \Config::get('locale')
				))
			));
		}
	}
	
	public function get() {
		Profiler::checkpoint('ORM get');
		$models = array();
		$ids = array();
		$current_model = $this->model;

		$dal = $this->getDAL();

		$rows = $dal->get();
		foreach($rows as $row) {
			if(!$row['id'])
				continue;
			$models[] = $this->toModel($row);
			$ids[] = $row['id'];
		}
		
		if(sizeof($models) && sizeof($this->with)) {
			foreach($this->with as $relation_name=>$closure) {
				$rel = $current_model::getDefinition()->relations[$relation_name];
				$relation_type = $rel['type'];
				$relation_model = $rel['model'];

				switch($relation_type) {
					case 'hasOne':
					case 'belongsTo':
						$link = $rel['link'];
						
						$res = $relation_model::where(array('id IN ('.implode(', ', $ids).')'))->get();
						foreach($models as $model) {
							$id = $model->$link;
							$filter = array_filter($res, function($result) use ($id) {
								return ($id == $result->id);
							});
							if(isset($filter[0]))
								$model->$relation_name = $filter[0];
							else
								$model->$relation_name = null;
						}
						break;
					case 'hasMany':
						$link = $rel['link'];
						
						$orm = $relation_model::where(array($link.' IN ('.implode(', ', $ids).')'));
						if(is_callable($closure))
							$closure($orm);
						$res = $orm->get();
						foreach($models as $model) {
							$id = $model->id;
							$model->$relation_name = array_filter($res, function($result) use ($id, $link) {
								return ($id == $result->$link);
							});
						}
						break;
					case 'HMABT':
						$join_table = $rel['join_table'];
						$currentmodel_idfield = $rel['link_a'];
						$relationmodel_idfield = $rel['link_b'];

						$reverse_relation = $rel->reverse();
						$reverse_relation_name = $reverse_relation['name'];

						$orm = $relation_model::join($reverse_relation_name)
							->where(array(
								$current_model::getTable().'.id IN ('.implode(', ', $ids).')',
							));

						if(is_callable($closure))
							$closure($orm);
						$res = $orm->getDAL()->addSelect($join_table.'.'.$currentmodel_idfield.' as __ormrelid')->groupBy(null)->get();
						foreach($models as $model) {
							$id = $model->id;
							$filter = array_filter($res, function($result) use ($id) {
								return $id == $result['__ormrelid'];
							});
							$mres = array();
							foreach($filter as $m)
								$mres[] = $this->toModel($m, $relation_model);
							$model->$relation_name = $mres;
						}
						break;
					default:
						throw new \Exception('Relation type '.$relation_type.' does not exist');
				}
			}
		}
		
		return $models;
	}
	
	public function selectQuery($sql, $args=array()) {
		$models = array();
		$model = $this->model;
		
		$dal = new DAL;
		$rows = $dal->query($sql, $args)->all();
		foreach($rows as $row)
			$models[] = ORMHandler::unserializeSet(new $model, $row);
			
		return $models;
	}
	
	public function paginate($page, $per_page=10, &$paginator=null) {
		$page = $page ? $page:1;
		$this->offset(($page-1)*$per_page);
		$this->limit($per_page);
		$paginator = new \Coxis\Utils\Paginator($per_page, $this->count(), $page);
		
		return $this->get();
	}
	
	public function with($with, $closure=null) {
		Profiler::checkpoint('ORM with');
		$this->with[$with] = $closure;
		
		return $this;
	}

	protected function replaceTable($sql) {
		$model = $this->model;
		$table = $this->getTable();
		$i18nTable = $this->geti18nTable();
		preg_match_all('/(?<![\.a-z0-9-_`\(\)])([a-z0-9-_]+)(?![\.a-z0-9-_`\(\)])/', $sql, $matches);
		foreach($matches[0] as $property) {
			if(!$model::hasProperty($property))
				continue;
			$table = $model::property($property)->i18n ? $i18nTable:$table;
			$sql = preg_replace('/(?<![\.a-z0-9-_`\(\)])('.$property.')(?![\.a-z0-9-_`\(\)])/', $table.'.`$1`', $sql);
		}
		// $sql = preg_replace('/([a-zA-Z0-9-_]+)\.([a-zA-Z0-9-_]+)/', '$1.`$2`', $sql);
		#todo, was that really useful?

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
		while($model = $this->next())
			$count += $model->destroy();

		return $count;
	}
	
	public function update($values) {
		while($model = $this->next())
			$model->save($values);
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
