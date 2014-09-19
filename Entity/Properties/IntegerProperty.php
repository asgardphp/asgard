<?php
namespace Asgard\Entity\Properties;

/**
 * Integer Property.
 */
class IntegerProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritdoc}
	 */
	public function getSQLType() {
		if($this->get('length'))
			return 'int('.$this->get('length').')';
		else
			return 'int(11)';
	}
}