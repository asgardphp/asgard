<?php
namespace Asgard\Orm\Rules;

/**
 * Verify that there is at least 1 entity.
 */
class Relationrequired extends \Asgard\Validation\Rule {
	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		$entity = $validator->get('entity');
		$dataMapper = $validator->get('dataMapper');
		$relation = $validator->getName();
		if($entity->data['properties'][$relation] instanceof \Asgard\Entity\ManyCollection)
			return $entity->data['properties'][$relation]->count() > 0;
		else
			return $dataMapper->related($entity, $relation)->count() > 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute is required.';
	}
}