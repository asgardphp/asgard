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
		$attr = $validator->getName();
		return $entity::where(['and' => [$attr=>$input, 'id!=?'=>$entity->id]])->count() == 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMessage() {
		return ':attribute must be unique.';
	}
}