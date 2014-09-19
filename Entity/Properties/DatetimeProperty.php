<?php
namespace Asgard\Entity\Properties;

/**
 * Datetime Property.
 */
class DatetimeProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritdoc}
	 */
	public function getRules() {
		$rules = parent::getRules();
		$rules['isinstanceof'] = 'Carbon\Carbon';

		return $rules;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMessages() {
		$messages = parent::getMessages();
		$messages['instanceof'] = ':attribute must be a valid datetime.';

		return $messages;
	}

	/**
	 * {@inheritdoc}
	 */
	public function _getDefault() {
		return \Carbon\Carbon::now();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doSerialize($obj) {
		if($obj == null)
			return '';
		return $obj->format('Y-m-d H:i:s');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doUnserialize($str) {
		return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $str);
	}

	/**
	 * {@inheritdoc}
	 */
	public function doSet($val) {
		if($val instanceof \Carbon\Carbon)
			return $val;
		elseif(is_string($val)) {
			try {
				return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $val);
			} catch(\Exception $e) {
				return $val;
			}
		}
		return $val;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSQLType() {
		return 'datetime';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormField() {
		return 'Asgard\Form\Fields\DatetimeField';
	}
}