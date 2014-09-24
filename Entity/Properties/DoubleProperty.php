<?php
namespace Asgard\Entity\Properties;

/**
 * Double Property.
 */
class DoubleProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function getSQLType() {
		return 'double';
	}
}