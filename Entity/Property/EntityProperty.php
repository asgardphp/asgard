<?php
namespace Asgard\Entity\Property;

/**
 * Entity Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class EntityProperty extends \Asgard\Entity\Property {
	public function _prepareValidator(\Asgard\Validation\ValidatorInterface $validator) {
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
		if($val !== null && !$val instanceof \Asgard\Entity\Entity)
			throw new \Exception($name.' must be an entity.');
		else
			return $val;
	}
}