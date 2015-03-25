<?php
namespace Asgard\Entity\Properties;

/**
 * Text Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class TextProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function getORMParameters() {
		return [
			'type' => 'text',
			'length' => $this->get('length') ? $this->get('length'):65535,
		];
	}
}