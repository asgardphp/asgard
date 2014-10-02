<?php
namespace Asgard\Orm;

/**
 * Define relation between entities.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class EntityRelation {
	/**
	 * Entity class.
	 * @var string
	 */
	protected $entityClass;
	/**
	 * Entity definition.
	 * @var \Asgard\Entity\EntityDefinition
	 */
	protected $entityDefinition;
	/**
	 * DataMapper.
	 * @var DataMapperInterface
	 */
	protected $dataMapper;
	/**
	 * Reverse relation parameters.
	 * @var EntityRelation
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
	protected $params = [];

	/**
	 * Constructor.
	 * @param \Asgard\Entity\EntityDefinition $entityDefinition
	 * @param DataMapperInterface             $dataMapper
	 * @param string                          $name
	 * @param array                           $params
	 */
	public function __construct(\Asgard\Entity\EntityDefinition $entityDefinition, DataMapperInterface $dataMapper, $name, array $params) {
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
			return $this->reverse()->get('name').'_id';
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
		return $this->getTargetDefinition()->getShortName().'_id';
	}

	/**
	 * Return the position field name.
	 * @return string
	 */
	public function getPositionField() {
		return $this->getTargetDefinition()->getShortName().'_position';
	}

	/**
	 * Get the table of a HMABT table.
	 * @param  string $prefix table prefix
	 * @return string
	 */
	public function getTable($prefix=null) {
		$entityShortName = $this->entityDefinition->getShortName();
		$relationEntityShortName = $this->getTargetDefinition()->getShortName();

		if($entityShortName < $relationEntityShortName)
			return $prefix.$entityShortName.'_'.$relationEntityShortName;
		else
			return $prefix.$relationEntityShortName.'_'.$entityShortName;
	}

	/**
	 * Return the target entity definition.
	 * @return \Asgard\Entity\EntityDefinition
	 */
	public function getTargetDefinition() {
		#todo polymorphism, only for entities with one related entity
		// if($relation['polymorphic'])
		// 	$relation_entity = $relation['real_entity'];
		// else
		// 	$relation_entity = $relation->get('entity');
		return $this->entityDefinition->getEntitiesManager()->get($this->params['entity']);
	}

	/**
	 * Get the relation type.
	 * @return string   hasOne, belongsTo, hasMany or HMABT
	 */
	public function type() {
		$rev = $this->reverse();

		if($this->get('many')) {
			if($rev->get('many'))
				return 'HMABT';
			else
				return 'hasMany';
		}
		else {
			if($rev->get('many'))
				return 'belongsTo';
			else
				return 'hasOne';
		}
	}

	/**
	 * Get the reverse relation instance.
	 * @return EntityRelation
	 */
	public function reverse() {
		if($this->reverseRelation !== null)
			return $this->reverseRelation;

		$origEntityName = strtolower($this->entityClass);
		$entityName = preg_replace('/^\\\/', '', $origEntityName);

		$relationEntityDefinition = $this->getTargetDefinition();
		$name = $this->name;

		$rev_relations = [];
		foreach($this->dataMapper->relations($relationEntityDefinition) as $rev_rel_name=>$rev_rel) {
			$relEntityClass = preg_replace('/^\\\/', '', strtolower($rev_rel->get('entity')));

			if($relEntityClass == $entityName
				|| $this->get('as') == $rev_rel->get('entity')
				) {
				if($rev_rel_name == $name)
					continue;
				if($this->get('for')!==null && $this->get('for')!==$rev_rel_name)
					continue;
				if($rev_rel->get('for')!==null && $rev_rel->get('for')!==$name)
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

	/**
	 * Return a parameter.
	 * @param  string $name
	 * @return mixed
	 */
	public function get($name) {
		if(!isset($this->params[$name]))
			return;
		return $this->params[$name];
	}

	/**
	 * Set a parameter.
	 * @param string $name
	 * @param mixed  $value
	 * @return EntityRelation $this
	 */
	public function set($name, $value) {
		$this->params[$name] = $value;
		return $this;
	}
}