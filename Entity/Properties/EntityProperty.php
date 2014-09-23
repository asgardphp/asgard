<?php
namespace Asgard\Entity\Properties;

/**
 * Entity Property.
 */
class EntityProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritdoc}
	 */
	public function getDefault($entity, $name) {
		return null;
	}
}