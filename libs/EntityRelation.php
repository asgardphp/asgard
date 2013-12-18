<?php
class EntityRelation implements \ArrayAccess {
	protected $entityClass;
	public $name;
	public $params = array();

	function __construct($entityDefinition, $name, $params) {
		$entityClass = $entityDefinition->getClass();
		$this->entityClass = $entityClass;
		$this->params = $params;
		$this->params['name'] = $this->name = $name;

		if(isset($params['polymorphic']) && $params['polymorphic']) {
			#No hasMany/HMABT for polymorphic
			$this->params['link'] = $name.'_id';
			$this->params['link_type'] = $name.'_type';

			$entityDefinition->addProperty($this->params['link'], array('type' => 'integer', 'required' => (isset($this->params['required']) && $this->params['required']), 'editable'=>false));
			$entityDefinition->addProperty($this->params['link_type'], array('type' => 'text', 'required' => (isset($this->params['required']) && $this->params['required']), 'editable'=>false));
		}
		else {
			$rev = $this->reverseRelationParams();
			$relation_entity = $this->params['entity'];

			if(!isset($this->params['type'])) {
				if($this->params['has'] == 'one') {
					if($rev['has'] == 'one')
						$this->params['type'] = 'hasOne';
					elseif($rev['has'] == 'many')
						$this->params['type'] = 'belongsTo';
				}
				elseif($this->params['has'] == 'many') {
					if($rev['has'] == 'one')
						$this->params['type'] = 'hasMany';
					elseif($rev['has'] == 'many')
						$this->params['type'] = 'HMABT';
				}
			}

			if(!isset($this->params['type']))
				throw new \Exception('Problem with relation type');

			if($this->params['type'] == 'hasMany') {
				$rev_rel = $this->reverseRelationParams();
				$this->params['link'] = $rev_rel['name'].'_id';
			}
			elseif($this->params['type'] == 'HMABT') {
				$this->params['link_a'] = $entityClass::getEntityName().'_id';
				$this->params['link_b'] = $relation_entity::getEntityName().'_id';
				if(isset($this->params['sortable']) && $this->params['sortable'])
					$this->params['sortable'] = $entityClass::getEntityName().'_position';
				else
					$this->params['sortable'] = false;
				if($entityClass::getEntityName() < $relation_entity::getEntityName())
					$this->params['join_table'] = \Config::get('database/prefix').$entityClass::getEntityName().'_'.$relation_entity::getEntityName();
				else
					$this->params['join_table'] = \Config::get('database/prefix').$relation_entity::getEntityName().'_'.$entityClass::getEntityName();
			}
			else {
				$this->params['link'] = $name.'_id';
				$entityDefinition->addProperty($this->params['link'], array('type' => 'integer', 'required' => (isset($this->params['required']) && $this->params['required']), 'editable'=>false));
			}
		}
	}

	protected function reverseRelationParams() {
		$origEntityName = strtolower($this->entityClass);
		$entityName = preg_replace('/^\\\/', '', $origEntityName);

		$relation_entity = $this->params['entity'];
		$name = $this->name;

		$rev_relations = array();
		if(isset($relation_entity::$relations))
			foreach($relation_entity::$relations as $rev_rel_name=>$rev_rel) {
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
					$rev_relations[] = array_merge(array('name'=>$rev_rel_name), $rev_rel);
				}
			}

		if(count($rev_relations) == 0)
			throw new \Exception('No reverse relation for '.$entityName.': '.$name);
		elseif(count($rev_relations) > 1)
			throw new \Exception('Multiple reverse relations for '.$entityName.': '.$name);
		else
			return $rev_relations[0];
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
}