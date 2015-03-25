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
	public function __construct(array $params) {
		if(!isset($params['length']))
			$params['length'] = 65535;
		parent::__construct($params);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getORMParameters() {
		return [
			'type' => 'text',
			'length' => $this->get('length'),
		];
	}
}