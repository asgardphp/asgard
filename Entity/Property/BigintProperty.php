<?php
namespace Asgard\Entity\Property;

/**
 * Bigint Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class BigintProperty extends IntegerProperty {
	/**
	 * Return parameters for ORM.
	 * @return array
	 */
	public function getORMParameters() {
		return [
			'type' => 'bigint',
		];
	}
}