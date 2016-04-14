<?php
namespace Asgard\Entity\Property;

/**
 * Entity Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class EntityProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function prepareValidator(\Asgard\Validation\ValidatorInterface $validator) {
		parent::prepareValidator($validator);
		if($this->get('entity'))
			$validator->rule('isinstanceof', $this->get('entity'));
		elseif($this->get('entities')) {
			$validators = [];
			foreach($this->get('entities') as $class)
				$validators[] = $this->definition->getEntityManager()->createValidator()->rule('isinstanceof', $class);
			$validator->rule('any', $validators);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function doSet($val, \Asgard\Entity\Entity $entity, $name) {
		if(is_array($val)) {
			if($entity->getDefinition()->property($name)->get('entities')) {
				$class = $val[0];
				$id = $val[1];
				return $entity->getDefinition()->getEntityManager()->make($class, ['id'=>$id]);
			}
		}
		else
			return $val;
	}
}