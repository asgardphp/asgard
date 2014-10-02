<?php
namespace Asgard\Entity\Properties;

/**
 * Longtext Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class LongtextProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function getSQLType() {
		return 'text';
	}
}