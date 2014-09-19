<?php
namespace Asgard\Entity\Properties;

/**
 * Longtext Property.
 */
class LongtextProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritdoc}
	 */
	public function getSQLType() {
		return 'text';
	}
}