<?php
namespace Asgard\Entity\Properties;

/**
 * Double Property.
 */
class DoubleProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritdoc}
	 */
	public function getSQLType() {
		return 'double';
	}
}