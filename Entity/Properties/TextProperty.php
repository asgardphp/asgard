<?php
namespace Asgard\Entity\Properties;

/**
 * Text Property.
 */
class TextProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritdoc}
	 */
	public function getSQLType() {
		if($this->get('length'))
			return 'varchar('.$this->get('length').')';
		else
			return 'varchar(255)';
	}
}