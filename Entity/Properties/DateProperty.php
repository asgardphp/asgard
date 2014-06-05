<?php
namespace Asgard\Entity\Properties;

class DateProperty extends \Asgard\Entity\Property {
	public function getRules() {
		$rules = parent::getRules();
		$rules['isinstanceof'] = 'Carbon\Carbon';

		return $rules;
	}

	public function getMessages() {
		$messages = parent::getMessages();
		$messages['instanceof'] = ':attribute must be a valid date.';

		return $messages;
	}

	public function _getDefault() {
		return \Carbon\Carbon::now();
	}

	protected function doSerialize($obj) {
		if($obj === null)
			return '';
		return $obj->format('Y-m-d');
	}

	protected function doUnserialize($str) {
		if(!$str)
			return new \Carbon\Carbon();
		return \Carbon\Carbon::createFromFormat('Y-m-d', $str);
	}

	protected function doSet($val) {
		if($val instanceof \Carbon\Carbon)
			return $val;
		elseif(is_string($val)) {
			try {
				return \Carbon\Carbon::createFromFormat('Y-m-d', $val);
			} catch(\Exception $e) {}
		}
		return $val;
	}

	public function getSQLType() {
		return 'date';
	}

	public function toString($obj) {
		return $obj->format('Y-m-d');
	}
}