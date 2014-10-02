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
}