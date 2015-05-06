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
			'precision' => 20,
			'scale' => 6,
		];
	}
}