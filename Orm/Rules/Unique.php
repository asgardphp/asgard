<?php
namespace Asgard\Orm\Rules;

class Unique extends \Asgard\Validation\Rule {
	public function validate($input, $parentInput, $validator) {
		$entity = $validator->get('entity');
		$attr = $validator->getName();
		return $entity::where(['and' => [$attr=>$input, 'id!=?'=>$entity->id]])->count() == 0;
	}

	public function getMessage() {
		return ':attribute must be unique.';
	}
}