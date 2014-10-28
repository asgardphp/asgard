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
	 * @var \Asgard\Entity\Definition
	 */
	protected $definition;
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
	protected $name;
	/**
	 * Parameters.
	 * @var array
	 */
	protected $params = [];
	/**
	 * Target entity definition. Necessary when dealing with polymorphic relations.
	 * @var \Asgard\Entity\Definition
	 */
	protected $targetDefinition;

	/**
	 * Constructor.
	 * @param \Asgard\Entity\Definition $Definition
	 * @param DataMapperInterface             $dataMapper
	 * @param string                          $name
	 * @param array                           $params
	 */
	public function __construct(\Asgard\Entity\Definition $Definition, DataMapperInterface $dataMapper, $name, array $params) {
		$entityClass            = $Definition->getClass();
		$this->entityClass      = $entityClass;
		$this->definition = $Definition;
		$this->dataMapper       = $dataMapper;
		if(isset($params['relation_type']) && ($params['relation_type'] == 'hasMany' || $params['relation_type'] == 'HMABT'))
			$params['many'] = true;
		$this->params           = $params;
		$this->params['name']   = $this->name = $name;
	}

	/**
	 * Check if the relation is polymorphic.
	 * @return boolean
	 */
	public function isPolymorphic() {
		return $this->get('entities') !== null;
	}

	/**
	 * Return the name.
	 * @return string.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get the relation link attribute.
	 * @return string
	 */
	public function getLink() {
		if($this->get('many') && $this->type() == 'hasMany')
			return $this->reverse()->get('name').'_id';
		elseif(!$this->get('many'))
			return $this->name.'_id';
	}

	/**
	 * Get the relation link attribute.
	 * @return string
	 */
	public function getLinkType() {
		if(!$this->isPolymorphic())
			return;
		if($this->get('as'))
			return $this->get('as').'_type';
		else
			return $this->name.'_type';
	}

	/**
	 * Get the link A for a HMABT relation.
	 * @return string
	 */
	public function getLinkA() {
		if($this->reverse()->isPolymorphic())
			return $this->reverse()->get('as').'_id';
		else
			return $this->reverse()->getName().'_id';
	}

	/**
	 * Get the link B for a HMABT relation.
	 * @return string
	 */
	public function getLinkB() {
		if($this->isPolymorphic())
			return $this->get('as').'_id';
		else
			return $this->getName().'_id';
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
	public function getAssociationTable($prefix=null) {
		if($this->type() !== 'HMABT')
			throw new \Exception('Association table can only be used for HMABT relations.');

		if(!$this->isPolymorphic() && $this->reverse()->isPolymorphic())
			$entityShortName = $this->reverse()->get('as');
		else
			$entityShortName = $this->definition->getShortName();

		if($this->isPolymorphic())
			$relationEntityShortName = $this->get('as');
		else
			$relationEntityShortName = $this->getTargetDefinition()->getShortName();

		if($entityShortName < $relationEntityShortName)
			return $prefix.$entityShortName.'_'.$relationEntityShortName;
		else
			return $prefix.$relationEntityShortName.'_'.$entityShortName;
	}

	/**
	 * Return the target entity definition.
	 * @return \Asgard\Entity\Definition
	 */
	public function getTargetDefinition() {
		if($this->targetDefinition)
			return $this->targetDefinition;

		if(isset($this->params['entities']))
			$entityClass = $this->params['entities'][0];
		else
			$entityClass = $this->params['entity'];

		return $this->definition->getEntityManager()->get($entityClass);
	}

	/**
	 * Set the target definition. Necessary when dealing with polymorphic relations.
	 * @param  \Asgard\Entity\Definition $targetDefinition
	 * @return static  $this
	 */
	public function setTargetDefinition(\Asgard\Entity\Definition $targetDefinition) {
		$this->targetDefinition = $targetDefinition;
		return $this;
	}

	/**
	 * Get the relation type.
	 * @return string hasOne, belongsTo, hasMany or HMABT
	 */
	public function type() {
		if($this->get('relation_type'))
			return $this->get('relation_type');
		elseif($this->get('poymorphic'))
			throw new \Exception('Parameter relation_type must be provided for polymorphic relations.');

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

		$relationDefinition = $this->getTargetDefinition();
		$name = $this->name;

		$rev_relations = [];
		foreach($this->dataMapper->relations($relationDefinition) as $rev_rel_name=>$rev_rel) {
			$relEntityClass = preg_replace('/^\\\/', '', strtolower($rev_rel->get('entity')));

			if($relEntityClass == $entityName || $this->get('as') == $rev_rel->get('entity')) {
				if($rev_rel_name == $name)
					continue;
				if($this->get('for') !== null && $this->get('for') !== $rev_rel_name)
					continue;
				if($rev_rel->get('for') !== null && $rev_rel->get('for') !== $name)
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
	public function prepareValidator(\Asgard\Validation\ValidatorInterface $validator) {
		$validator->rules(isset($this->params['ormValidation']) ? $this->params['ormValidation']:[]);
		if(isset($this->params['required']))
			$validator->rule('ormrequired', $this->params['required']);
		if(isset($this->params['validation'])) {
			foreach($this->params['validation'] as $name=>$params) {
				if(is_integer($name)) {
					$name = $params;
					$params = [];
				}
				$validator->rule('orm'.$name, $params); #prefix each rule with suffix "orm"
			}
		}
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