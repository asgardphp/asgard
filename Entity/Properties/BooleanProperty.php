<?php
namespace Asgard\Entity\Properties;

/**
 * Boolean Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class BooleanProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function prepareValidator(\Asgard\Validation\ValidatorInterface $validator) {
		parent::prepareValidator($validator);
		$validator->rule('required', false);
	}

	/**
	 * {@inheritDoc}
	 */
	public function _getDefault() {
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFormField() {
		return 'Asgard\Form\Fields\BooleanField';
	}

	/**
	 * {@inheritDoc}
	 */
	public function toString($val) {
		return $val ? '1':'0';
	}

	/**
	 * Return parameters for ORM.
	 * @return array
	 */
	public function getORMParameters() {
		return [
			'type' => 'integer',
			'length' => 1,
		];
	}

	/**
	 * Return prepared input for SQL.
	 * @param  mixed $val
	 * @return integer
	 */
	public function toSQL($val) {
		return $val ? 1:0;
	}

	/**
	 * Transform SQL output.
	 * @param  mixed $val
	 * @return boolean
	 */
	public function fromSQL($val) {
		return (bool)$val;
	}
}