<?php
namespace Asgard\Entity\Properties;

/**
 * Double Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class DoubleProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function getSQLType() {
		return 'double';
	}
}