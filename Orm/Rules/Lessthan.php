<?php
namespace Asgard\Orm\Rules;

/**
 * Verify that there are less than x entities.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Lessthan extends \Asgard\Validation\Rule {
	/**
	 * Maximum number of entities
	 * @var integer
	 */
	public $less;

	/**
	 * Constructor.
	 * @param integer $less
	 */
	public function __construct($less) {
		$this->less = $less;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		$entity = $validator->get('entity');
		$dataMapper = $validator->get('dataMapper');
		$relation = $validator->getName();
		if($entity->data['properties'][$relation] instanceof \Asgard\Entity\ManyCollection)
			return $entity->data['properties'][$relation]->count() < $this->less;
		else
			return $dataMapper->related($entity, $relation)->count() < $this->less;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must have less than :less elements.';
	}
}