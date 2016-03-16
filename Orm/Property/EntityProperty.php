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
	public function prepareValidator(\Asgard\Validation\ValidatorInterface $validator) {
		parent::prepareValidator($validator);

		if($rules = $this->get('ormValidation'))
			$validator->rules($rules);
		$validator->isNull(function(){return false;});
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