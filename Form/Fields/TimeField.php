<?php
namespace Asgard\Form\Fields;

class TimeField extends \Asgard\Form\Field {
	protected $widget = 'time';
	protected $data_type = 'date';

	public function setValue($value) {
		if(is_array($value) && isset($value['hour']) && isset($value['minute']) && isset($value['second']))
			$this->value = \Carbon\Carbon::createFromTime($value['hour'], $value['minute'], $value['second']);
		elseif(is_string($value))
			$this->value = \Carbon\Carbon::createFromFormat('Y-m-d', $value);
	}

	public function value() {
		if(isset($this->options['data_type'])) {
			if($this->options['data_type'] == 'date')
				return $this->value;
			elseif($this->options['data_type'] == 'string')
				return $this->value->format('Y-m-d H:i:s');
		}
		return $this->value;
	}

	public function getHour() {
		if(!$this->value)
			return null;
		return $this->value->format('H');
	}

	public function getMinute() {
		if(!$this->value)
			return null;
		return $this->value->format('i');
	}

	public function getSecond() {
		if(!$this->value)
			return null;
		return $this->value->format('s');
	}
}