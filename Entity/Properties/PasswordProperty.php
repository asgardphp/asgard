<?php
namespace Asgard\Entity\Properties;

/**
 * Password Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class PasswordProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function getORMParameters() {
		return [
			'type' => 'string',
		];
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
}