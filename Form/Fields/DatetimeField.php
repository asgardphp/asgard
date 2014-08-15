<?php
namespace Asgard\Form\Fields;

class DatetimeField extends \Asgard\Form\Field {
	protected $widget = 'datetime';
	protected $data_type = 'date';

	public function setValue($value) {
		if(is_array($value) && isset($value['year']) && isset($value['month']) && isset($value['day']) && isset($value['hour']) && isset($value['minute']) && isset($value['second']))
			$this->value = \Carbon\Carbon::create($value['year'], $value['month'], $value['day'], $value['hour'], $value['minute'], $value['second']);
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

	public function getDay() {
		if(!$this->value)
			return null;
		return $this->value->format('d');
	}

	public function getMonth() {
		if(!$this->value)
			return null;
		return $this->value->format('m');
	}

	public function getYear() {
		if(!$this->value)
			return null;
		return $this->value->format('Y');
	}
}