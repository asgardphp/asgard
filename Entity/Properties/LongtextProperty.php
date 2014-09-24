<?php
namespace Asgard\Entity\Properties;

/**
 * Longtext Property.
 */
class LongtextProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function getSQLType() {
		return 'text';
	}
}