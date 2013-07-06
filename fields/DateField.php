<?php
class DateField extends \Coxis\Form\Fields\Field {
	protected $default_render = 'date';
	protected $data_type = 'date';

	public function setValue($value) {
		if(is_array($value))
			$this->value = new Date(mktime(0, 0, 0, $value['month'], $value['day'], $value['year']));
		else
			$this->value = Date::fromDate($value);
	}

	public function getValue() {
		if(isset($this->options['data_type']))
			if($this->options['data_type'] == 'date')
				return $this->value;
			elseif($this->options['data_type'] == 'string')
				return $this->value->format('d/m/Y');
		return $this->value;
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