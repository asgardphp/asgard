<?php
namespace Asgard\Entity\Properties;

/**
 * Date Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class DateProperty extends \Asgard\Entity\Property {
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
		$messages['instanceof'] = ':attribute must be a valid date.';

		return $messages;
	}

	/**
	 * {@inheritDoc}
	 */
	public function _getDefault() {
		return \Asgard\Common\Date::now();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doSerialize($obj) {
		if($obj === null)
			return '';
		return $obj->format('Y-m-d');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doUnserialize($str) {
		if(!$str)
			return new \Asgard\Common\Date;
		return \Asgard\Common\Date::createFromFormat('Y-m-d', $str);
	}

	/**
	 * {@inheritDoc}
	 */
	public function doSet($val, \Asgard\Entity\Entity $entity, $name) {
		if($val instanceof \Asgard\Common\DatetimeInterface)
			return $val;
		elseif(is_string($val)) {
			try {
				return \Asgard\Common\Date::createFromFormat('Y-m-d', $val);
			} catch(\Exception $e) {}
		}
		return $val;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSQLType() {
		return 'date';
	}

	/**
	 * {@inheritDoc}
	 */
	public function toString($obj) {
		return $obj->format('Y-m-d');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFormField() {
		return 'Asgard\Form\Fields\DateField';
	}
}