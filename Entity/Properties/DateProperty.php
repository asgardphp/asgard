<?php
namespace Asgard\Entity\Properties;

/**
 * Date Property.
 */
class DateProperty extends \Asgard\Entity\Property {
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
		$messages['instanceof'] = ':attribute must be a valid date.';

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
		if($obj === null)
			return '';
		return $obj->format('Y-m-d');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doUnserialize($str) {
		if(!$str)
			return new \Carbon\Carbon();
		return \Carbon\Carbon::createFromFormat('Y-m-d', $str);
	}

	/**
	 * {@inheritdoc}
	 */
	public function doSet($val) {
		if($val instanceof \Carbon\Carbon)
			return $val;
		elseif(is_string($val)) {
			try {
				return \Carbon\Carbon::createFromFormat('Y-m-d', $val);
			} catch(\Exception $e) {}
		}
		return $val;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSQLType() {
		return 'date';
	}

	/**
	 * {@inheritdoc}
	 */
	public function toString($obj) {
		return $obj->format('Y-m-d');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormField() {
		return 'Asgard\Form\Fields\DateField';
	}
}