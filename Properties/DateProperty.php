<?php
namespace Asgard\Core\Properties;

class DateProperty extends BaseProperty {
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

	public function serialize($obj) {
		if($obj == null)
			return '';
		return $obj->format('Y-m-d');
	}

	public function unserialize($str) {
		return \Carbon\Carbon::createFromFormat('Y-m-d', $str);
	}

	public function set($val) {
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