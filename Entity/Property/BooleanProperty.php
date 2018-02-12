<?php
namespace Asgard\Entity\Property;

/**
 * Boolean Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class BooleanProperty extends \Asgard\Entity\Property {
	public function _prepareValidator(\Asgard\Validation\ValidatorInterface $validator) {
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
		return 'Asgard\Form\Field\BooleanField';
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
			'type' => 'boolean',
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