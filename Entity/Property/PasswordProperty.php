<?php
namespace Asgard\Entity\Property;

/**
 * Password Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class PasswordProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function getFormParameters() {
		$params = $this->get('form');
		if(!isset($params['hidden']))
			$params['hidden'] = true;
		return $params;
	}

	/**
	 * {@inheritDoc}
	 */
	public function doSet($val, \Asgard\Entity\Entity $entity, $name) {
		if($val === null)
			return null;
		try {
			$key = $entity->getDefinition()->getEntityManager()->getContainer()['config']['key'];
		} catch(\Exception $e) {
			$key = '';
		}
		return sha1($key.$val);
	}

	/**
	 * Return parameters for ORM.
	 * @return array
	 */
	public function getORMParameters() {
		return [
			'type' => 'string',
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