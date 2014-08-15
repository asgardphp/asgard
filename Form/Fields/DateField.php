<?php
namespace Asgard\Form\Fields;

class DateField extends \Asgard\Form\Field {
	protected $widget = 'date';
	protected $data_type = 'date';

	public function setValue($value) {
		if(is_array($value) && isset($value['year']) && isset($value['month']) && isset($value['day']))
			$this->value = \Carbon\Carbon::createFromDate($value['year'], $value['month'], $value['day']);
		elseif(is_string($value))
			$this->value = \Carbon\Carbon::createFromFormat('Y-m-d', $value);
	}

	public function value() {
		if(isset($this->options['data_type'])) {
			if($this->options['data_type'] == 'date')
				return $this->value;
			elseif($this->options['data_type'] == 'string')
				return $this->value->format('Y-m-d');
		}
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