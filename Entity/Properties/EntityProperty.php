<?php
namespace Asgard\Entity\Properties;

/**
 * Entity Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class EntityProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function getDefault($entity, $name) {
		return null;
	}

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
}