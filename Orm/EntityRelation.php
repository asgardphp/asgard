<?php
namespace Asgard\Orm;

class EntityRelation implements \ArrayAccess {
	protected $entityClass;
	protected $reverseRelation;
	public $name;
	public $params = [];

	public function __construct(\Asgard\Entity\EntityDefinition $entityDefinition, $name, array $params) {
		$entityClass = $entityDefinition->getClass();
		$this->entityClass = $entityClass;
		$this->params = $params;
		$this->params['name'] = $this->name = $name;

		if(isset($params['polymorphic']) && $params['polymorphic']) {
			#No hasMany/HMABT for polymorphic
			$this->params['link'] = $name.'_id';
			$this->params['link_type'] = $name.'_type';

			$entityDefinition->addProperty($this->params['link'], ['type' => 'integer', 'required' => (isset($this->params['required']) && $this->params['required']), 'editable'=>false]);
			$entityDefinition->addProperty($this->params['link_type'], ['type' => 'text', 'required' => (isset($this->params['required']) && $this->params['required']), 'editable'=>false]);
		}
		else {
			if($this->params['has'] == 'one') {
				$this->params['link'] = $name.'_id';
				$entityDefinition->addProperty($this->params['link'], ['type' => 'integer', 'required' => (isset($this->params['required']) && $this->params['required']), 'editable'=>false]);
			}
		}
	}

	public function getLink() {
		if($this->type() == 'hasMany') {
			return $this->reverseRelationParams()['name'].'_id';
		}
		elseif($this->type() == 'belongsTo') {
			return $this->name.'_id';
		}
	}

	public function getLinkA() {
		$entityClass = $this->entityClass;
		return $entityClass::getShortName().'_id';
	}

	public function getLinkB() {
		$entityClass = $this->params['entity'];
		return $entityClass::getShortName().'_id';
	}

	public function getTable($prefix=null) {
		$entityClass = $this->entityClass;
		$relationEntityClass = $this->params['entity'];

		if($entityClass::getShortName() < $relationEntityClass::getShortName())
			return $prefix.$entityClass::getShortName().'_'.$relationEntityClass::getShortName();
		else
			return $prefix.$relationEntityClass::getShortName().'_'.$entityClass::getShortName();
	}

	public function type() {
		$rev = $this->reverseRelationParams();

		if($this->params['has'] == 'one') {
			if($rev['has'] == 'one')
				return 'hasOne';
			elseif($rev['has'] == 'many')
				return 'belongsTo';
		}
		elseif($this->params['has'] == 'many') {
			if($rev['has'] == 'one')
				return 'hasMany';
			elseif($rev['has'] == 'many')
				return 'HMABT';
		}
		else
			throw new \Exception('Problem with relation type.');
	}

	protected function reverseRelationParams() {
		if($this->reverseRelation !== null)
			return $this->reverseRelation;

		$origEntityName = strtolower($this->entityClass);
		$entityName = preg_replace('/^\\\/', '', $origEntityName);

		$relation_entity = $this->params['entity'];
		$name = $this->name;

		$rev_relations = [];
		foreach($relation_entity::getDefinition()->relations() as $rev_rel_name=>$rev_rel) {
			$relEntityClass = preg_replace('/^\\\/', '', strtolower($rev_rel['entity']));

			if($relEntityClass == $entityName
				|| $this['as'] && $this['as'] == $rev_rel['entity']
				) {
				if($rev_rel_name == $name)
					continue;
				if(isset($relation['for']) && $relation['for']!=$rev_rel_name)
					continue;
				if(isset($rev_rel['for']) && $rev_rel['for']!=$name)
					continue;
				$rev_relations[] = $rev_rel;
			}
		}

		if(count($rev_relations) == 0)
			throw new \Exception('No reverse relation for '.$entityName.': '.$name);
		elseif(count($rev_relations) > 1)
			throw new \Exception('Multiple reverse relations for '.$entityName.': '.$name);
		else {
			$this->reverseRelation = $rev_relations[0];
			return $rev_relations[0];
		}
	}

	public function reverse() {
		$reverse_rel = $this->reverseRelationParams();
		$entity = $this->params['entity'];
		$rel_name = $reverse_rel['name'];
		return $entity::getDefinition()->relations[$rel_name];
	}

	public function offsetSet($offset, $value) {
		$this->params[$offset] = $value;
	}

	public function offsetExists($offset) {
		return isset($this->params[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->params[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->params[$offset]) ? $this->params[$offset] : null;
	}

	public function getRules() {
		$res = isset($this->params['validation']) ? $this->params['validation']:[];
		if(!is_array($res))
			$res = ['validation' => $res];
		if(isset($this->params['required']))
			$res['relationrequired'] = $this->params['required'];

		return $res;
	}
}