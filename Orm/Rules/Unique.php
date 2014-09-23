<?php
namespace Asgard\Orm\Rules;

/**
 * Verify that there is no other entity with the same attribute.
 */
class Unique extends \Asgard\Validation\Rule {
	/**
	 * {@inheritdoc}
	 */
	public function validate($input, $parentInput, $validator) {
		$entity = $validator->get('entity');
		$dataMapper = $validator->get('dataMapper');
		$attr = $validator->getName();
		$orm = $dataMapper->orm(get_class($entity))->where($attr, $input);
		if($entity->id !== null)
			$orm->where('id!=?', $entity->id);
		$dal = $orm->getDAL();
		return $orm->count() == 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMessage() {
		return ':attribute must be unique.';
	}
}