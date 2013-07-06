<?php
class ModelRelation implements \ArrayAccess {
	protected $modelClass;
	public $name;
	public $params = array();

		// if(isset($this->params['polymorphic']) && $this->params['polymorphic']) {
			
		// }
		// else {

	function __construct($modelDefinition, $name, $params) {
		$modelClass = $modelDefinition->getClass();
		$this->modelClass = $modelClass;
		$this->params = $params;
		$this->params['name'] = $this->name = $name;

		if(isset($params['polymorphic']) && $params['polymorphic']) {
			#No hasMany/HMABT for polymorphic
			$this->params['link'] = $name.'_id';
			$this->params['link_type'] = $name.'_type';

			$modelDefinition->addProperty($this->params['link'], array('type' => 'integer', 'required' => (isset($this->params['required']) && $this->params['required']), 'editable'=>false));
			$modelDefinition->addProperty($this->params['link_type'], array('type' => 'text', 'required' => (isset($this->params['required']) && $this->params['required']), 'editable'=>false));
		}
		else {
			$rev = $this->reverseRelationParams();
			$relation_model = $this->params['model'];

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
				$this->params['link_a'] = $modelClass::getModelName().'_id';
				$this->params['link_b'] = $relation_model::getModelName().'_id';
				if(isset($this->params['sortable']) && $this->params['sortable'])
					$this->params['sortable'] = $modelClass::getModelName().'_position';
				else
					$this->params['sortable'] = false;
				if($modelClass::getModelName() < $relation_model::getModelName())
					$this->params['join_table'] = \Config::get('database', 'prefix').$modelClass::getModelName().'_'.$relation_model::getModelName();
				else
					$this->params['join_table'] = \Config::get('database', 'prefix').$relation_model::getModelName().'_'.$modelClass::getModelName();
			}
			else {
				$this->params['link'] = $name.'_id';
				$modelDefinition->addProperty($this->params['link'], array('type' => 'integer', 'required' => (isset($this->params['required']) && $this->params['required']), 'editable'=>false));
			}
		}
	}

	protected function reverseRelationParams() {
		$origModelName = strtolower($this->modelClass);
		$modelName = preg_replace('/^\\\/', '', $origModelName);

		$relation_model = $this->params['model'];
		$name = $this->name;

		$rev_relations = array();
		if(isset($relation_model::$relations))
			foreach($relation_model::$relations as $rev_rel_name=>$rev_rel) {
				$relModelClass = preg_replace('/^\\\/', '', strtolower($rev_rel['model']));

				if($relModelClass == $modelName
					|| $this['as'] && $this['as'] == $rev_rel['model']
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
			throw new \Exception('No reverse relation for '.$modelName.': '.$name);
		elseif(count($rev_relations) > 1)
			throw new \Exception('Multiple reverse relations for '.$modelName.': '.$name);
		else
			return $rev_relations[0];
	}

	public function reverse() {
		$reverse_rel = $this->reverseRelationParams();
		$model = $this->params['model'];
		$rel_name = $reverse_rel['name'];
		return $model::getDefinition()->relations[$rel_name];
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