<?php
namespace Asgard\Orm;

/**
 * Define relation between entities.
 */
class EntityRelation implements \ArrayAccess {
	/**
	 * Entity class.
	 * @var string
	 */
	protected $entityClass;
	/**
	 * Entity definition.
	 * @var \Asgard\Entity\Definition
	 */
	protected $entityDefinition;
	/**
	 * DataMapper.
	 * @var DataMapper
	 */
	protected $dataMapper;
	/**
	 * Reverse relation parameters.
	 * @var array
	 */
	protected $reverseRelation;
	/**
	 * Relation name.
	 * @var string
	 */
	public $name;
	/**
	 * Parameters.
	 * @var array
	 */
	public $params = [];

	/**
	 * Constructor.
	 * @param \Asgard\Entity\EntityDefinition $entityDefinition
	 * @param string                          $name
	 * @param array                           $params
	 */
	public function __construct(\Asgard\Entity\EntityDefinition $entityDefinition, DataMapper $dataMapper, $name, array $params) {
		$entityClass = $entityDefinition->getClass();
		$this->entityClass = $entityClass;
		$this->entityDefinition = $entityDefinition;
		$this->dataMapper = $dataMapper;
		$this->params = $params;
		$this->params['name'] = $this->name = $name;
	}

	/**
	 * Get the relation link attribute.
	 * @return string
	 */
	public function getLink() {
		if($this->type() == 'hasMany')
			return $this->reverseRelationParams()['name'].'_id';
		elseif($this->type() == 'belongsTo' || $this->type() == 'hasOne')
			return $this->name.'_id';
	}

	/**
	 * Get the link A for a HMABT relation.
	 * @return string
	 */
	public function getLinkA() {
		return $this->entityDefinition->getShortName().'_id';
	}

	/**
	 * Get the link B for a HMABT relation.
	 * @return string
	 */
	public function getLinkB() {
		$entityClass = $this->params['entity'];
		return $this->entityDefinition->getEntitiesManager()->get($entityClass)->getShortName().'_id';
	}

	/**
	 * Get the table of a HMABT table.
	 * @param  string $prefix table prefix
	 * @return string
	 */
	public function getTable($prefix=null) {
		$relationEntityClass = $this->params['entity'];
		$entityShortName = $this->entityDefinition->getShortName();
		$relationEntityShortName = $this->entityDefinition->getEntitiesManager()->get($relationEntityClass)->getShortName();

		if($entityShortName < $relationEntityShortName)
			return $prefix.$entityShortName.'_'.$relationEntityShortName;
		else
			return $prefix.$relationEntityShortName.'_'.$entityShortName;
	}

	/**
	 * Get the relation type.
	 * @return string   hasOne, belongsTo, hasMany or HMABT
	 */
	public function type() {
		$rev = $this->reverseRelationParams();

		if($this['many']) {
			if($rev['many'])
				return 'HMABT';
			else
				return 'hasMany';
		}
		else {
			if($rev['many'])
				return 'belongsTo';
			else
				return 'hasOne';
		}
	}

	/**
	 * Get the reverse relation parameters.
	 * @return array
	 */
	protected function reverseRelationParams() {
		if($this->reverseRelation !== null)
			return $this->reverseRelation;

		$origEntityName = strtolower($this->entityClass);
		$entityName = preg_replace('/^\\\/', '', $origEntityName);

		$relation_entity = $this->params['entity'];
		$relationEntityDefinition = $this->entityDefinition->getEntitiesManager()->get($relation_entity);
		$name = $this->name;

		$rev_relations = [];
		foreach($this->dataMapper->relations($relationEntityDefinition) as $rev_rel_name=>$rev_rel) {
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

	/**
	 * Get the reverse relation instance.
	 * @return EntityRelation
	 */
	public function reverse() {
		$reverse_rel = $this->reverseRelationParams();
		$relation_entity = $this->params['entity'];
		$relationEntityDefinition = $this->entityDefinition->getEntitiesManager()->get($relation_entity);
		$rel_name = $reverse_rel['name'];
		return $this->dataMapper->getRelation($relationEntityDefinition, $rel_name);
	}

	/**
	 * Array set implementation.
	 * @param  string $offset
	 * @param  mixed  $value
	 */
	public function offsetSet($offset, $value) {
		$this->params[$offset] = $value;
	}

	/**
	 * Array exists implementation.
	 * @param  string $offset
	 */
	public function offsetExists($offset) {
		return isset($this->params[$offset]);
	}

	/**
	 * Array unset implementation.
	 * @param  string $offset
	 */
	public function offsetUnset($offset) {
		unset($this->params[$offset]);
	}

	/**
	 * Array get implementation.
	 * @param  string $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return isset($this->params[$offset]) ? $this->params[$offset] : null;
	}

	/**
	 * Get the relation validation rules.
	 * @return array
	 */
	public function getRules() {
		$res = isset($this->params['validation']) ? $this->params['validation']:[];
		if(!is_array($res))
			$res = ['validation' => $res];
		if(isset($this->params['required']))
			$res['relationrequired'] = $this->params['required'];

		return $res;
	}
}