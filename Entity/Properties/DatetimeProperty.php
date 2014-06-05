<?php
namespace Asgard\Entity\Properties;

class DatetimeProperty extends \Asgard\Entity\Property {
	public function getRules() {
		$rules = parent::getRules();
		$rules['isinstanceof'] = 'Carbon\Carbon';

		return $rules;
	}

	public function getMessages() {
		$messages = parent::getMessages();
		$messages['instanceof'] = ':attribute must be a valid datetime.';

		return $messages;
	}

	public function _getDefault() {
		return \Carbon\Carbon::now();
	}

	protected function doSerialize($obj) {
		if($obj == null)
			return '';
		return $obj->format('Y-m-d H:i:s');
	}

	protected function doUnserialize($str) {
		return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $str);
	}

	protected function doSet($val) {
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

	public function getSQLType() {
		return 'datetime';
	}
}