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
		$rules['isinstanceof'] = 'Carbon\Carbon';

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
		return \Carbon\Carbon::now();
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
			return new \Carbon\Carbon();
		return \Carbon\Carbon::createFromFormat('Y-m-d', $str);
	}

	/**
	 * {@inheritDoc}
	 */
	public function doSet($val, \Asgard\Entity\Entity $entity, $name) {
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