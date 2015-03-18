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
	public function __construct(array $params) {
		if(!isset($params['length']))
			$params['length'] = 255;
		parent::__construct($params);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getORMParameters() {
		return [
			'type' => 'string',
			'length' => $this->get('length')
		];
	}
}