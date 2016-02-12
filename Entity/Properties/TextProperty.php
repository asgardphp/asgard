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
	 * Return parameters for ORM.
	 * @return array
	 */
	public function getORMParameters() {
		return [
			'type' => 'text',
			'length' => $this->get('length'),
		];
	}

	/**
	 * Return prepared input for SQL.
	 * @param  mixed $val
	 * @return integer
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