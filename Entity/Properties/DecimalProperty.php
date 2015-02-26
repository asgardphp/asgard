<?php
namespace Asgard\Entity\Properties;

/**
 * Decimal Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class DecimalProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function getORMParameters() {
		return [
			'type' => 'decimal',
			'scale' => 6,
		];
	}
}