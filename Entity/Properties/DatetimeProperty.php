<?php
namespace Asgard\Entity\Properties;

/**
 * Datetime Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class DatetimeProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function prepareValidator(\Asgard\Validation\ValidatorInterface $validator) {
		parent::prepareValidator($validator);
		$validator->rule('isinstanceof', 'Asgard\Common\DatetimeInterface');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessages() {
		$messages = parent::getMessages();
		$messages['instanceof'] = ':attribute must be a valid datetime.';

		return $messages;
	}

	/**
	 * {@inheritDoc}
	 */
	public function _getDefault() {
		return;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doSerialize($obj) {
		if(!$obj)
			return null;
		return $obj->format('Y-m-d H:i:s');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doUnserialize($str) {
		if($str)
			return \Asgard\Common\Datetime::createFromFormat('Y-m-d H:i:s', $str);
	}

	/**
	 * {@inheritDoc}
	 */
	public function doSet($val, \Asgard\Entity\Entity $entity, $name) {
		if($val instanceof \Asgard\Common\DatetimeInterface)
			return $val;
		elseif(is_string($val))
			return \Asgard\Common\Datetime::createFromFormat('Y-m-d H:i:s', $val);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFormField() {
		return 'Asgard\Form\Fields\DatetimeField';
	}

	/**
	 * Return parameters for ORM.
	 * @return array
	 */
	public function getORMParameters() {
		return [
			'type' => 'datetime',
		];
	}

	/**
	 * Return prepared input for SQL.
	 * @param  mixed $val
	 * @return string
	 */
	public function toSQL($val) {
		return $val->format('Y-m-d H:i:s');
	}

	/**
	 * Transform SQL output.
	 * @param  mixed $val
	 * @return boolean
	 */
	public function fromSQL($val) {
		return $this->doUnserialize($val);
	}
}