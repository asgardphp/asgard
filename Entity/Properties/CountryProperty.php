<?php
namespace Asgard\Entity\Properties;

/**
 * Country Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CountryProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $params) {
		if(!isset($params['in']))
			$params['in'] = \Asgard\Common\Intl::singleton()->getCountryNames();
		parent::__construct($params);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getORMParameters() {
		return [
			'type' => 'string',
		];
	}
}