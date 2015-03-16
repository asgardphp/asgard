<?php
namespace Asgard\Entity\Properties;

/**
 * String Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class StringProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function getORMParameters() {
		return [
			'type' => 'string',
		];
	}
}