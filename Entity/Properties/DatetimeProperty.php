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
	public function getRules() {
		$rules = parent::getRules();
		$rules['isinstanceof'] = 'Asgard\Common\DatetimeInterface';

		return $rules;
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
		return \Asgard\Common\Datetime::now();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doSerialize($val) {
		if($val == null)
			return '';
		return $val->format('Y-m-d H:i:s');
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
		elseif(is_string($val)) {
			try {
				return \Asgard\Common\Datetime::createFromFormat('Y-m-d H:i:s', $val);
			} catch(\Exception $e) {
				return $val;
			}
		}
		return $val;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSQLType() {
		return 'datetime';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFormField() {
		return 'Asgard\Form\Fields\DatetimeField';
	}
}