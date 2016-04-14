<?php
namespace Asgard\Orm\Property;

/**
 * ORM Entity Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class EntityProperty extends \Asgard\Entity\Property\EntityProperty {
	/**
	 * Entity definition.
	 * @var \Asgard\Orm\DataMapperInterface
	 */
	protected $dataMapper;

	/**
	 * Constructor.
	 * @param array $params
	 */
	public function __construct(array $params, \Asgard\Orm\DataMapperInterface $dataMapper) {
		$this->params = $params;
		$this->dataMapper = $dataMapper;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefault($entity, $name) {
		if($this->get('many'))
			return new \Asgard\Orm\PersistentCollection($entity, $name, $this->dataMapper);
		else
			return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepareValidator(\Asgard\Validation\ValidatorInterface $validator) {
		parent::prepareValidator($validator);

		if($rules = $this->get('ormValidation'))
			$validator->rules($rules);
		if($this->get('required') !== null)
			$validator->rule('ormrequired', $this->get('required'));
		$validator->isNull(function(){return false;});
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDecorator($val, \Asgard\Entity\Entity $entity, $name, $silentException=false) {
		if($this->get('many')) {
			if(is_array($val)) {
				$res = new \Asgard\Orm\PersistentCollection($entity, $name, $this->dataMapper);
				foreach($val as $v)
					$res[] = $this->_doSet($v, $entity, $name, $silentException);
				return $res;
			}
			else
				throw new \Exception('Invalid type. Should be an array.');
		}
		else
			return $this->_doSet($val, $entity, $name, $silentException);
	}

	/**
	 * {@inheritDoc}
	 */
	public function doSet($val, \Asgard\Entity\Entity $entity, $name) {
		if(is_numeric($val)) {
			if($class = $entity->getDefinition()->property($name)->get('entity'))
				return $this->dataMapper->load($class, $val);
		}
		else
			return parent::doSet($val, $entity, $name);
	}
}