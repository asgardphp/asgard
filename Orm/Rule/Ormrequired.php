<?php
namespace Asgard\Orm\Rule;

/**
 * Verify that there is at least 1 entity.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Ormrequired extends \Asgard\Validation\Rule {
	/**
	 * {@inheritDoc}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		$entity = $validator->get('entity');
		$dataMapper = $validator->get('dataMapper');
		$attr = $validator->getName();
		$orm = $dataMapper->related($entity, $attr);

		return $orm->count() > 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage() {
		return ':attribute is required.';
	}
}