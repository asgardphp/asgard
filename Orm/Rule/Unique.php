<?php
namespace Asgard\Orm\Rule;

/**
 * Verify that there is no other entity with the same attribute.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Unique extends \Asgard\Validation\Rule {
	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		$entity = $validator->get('entity');
		$dataMapper = $validator->get('dataMapper');
		$attr = $validator->getName();
		$orm = $dataMapper->orm(get_class($entity))->where($attr, $input);
		if($entity->id !== null)
			$orm->where('id!=?', $entity->id);

		return $orm->count() == 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute must be unique.';
	}
}