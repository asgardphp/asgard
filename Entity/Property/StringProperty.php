<?php
namespace Asgard\Entity\Property;

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
	 * Return parameters for ORM.
	 * @return array
	 */
	public function getORMParameters() {
		return [
			'type' => 'string',
			'length' => $this->get('length')
		];
	}

	/**
	 * Return prepared input for SQL.
	 * @param  mixed $val
	 * @return string
	 */
	public function toSQL($val) {
		return (string)$val;
	}

	/**
	 * Transform SQL output.
	 * @param  mixed $val
	 * @return boolean
	 */
	public function fromSQL($val) {
		return (string)$val;
	}
}