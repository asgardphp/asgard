<?php
namespace Asgard\Entity\Properties;

/**
 * Email Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class EmailProperty extends TextProperty {
	/**
	 * {@inheritDoc}
	 */
	public function prepareValidator(\Asgard\Validation\ValidatorInterface $validator) {
		parent::prepareValidator($validator);
		$validator->rule('email');
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