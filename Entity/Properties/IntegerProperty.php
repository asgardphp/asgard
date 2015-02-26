<?php
namespace Asgard\Entity\Properties;

/**
 * Integer Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class IntegerProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function getORMParameters() {
		return [
			'type' => 'integer',
		];
	}
}