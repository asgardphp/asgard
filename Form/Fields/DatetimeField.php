<?php
namespace Asgard\Form\Fields;

/**
 * Datetime field.
 */
class DatetimeField extends \Asgard\Form\Field {
	/**
	 * {@inheritDoc}
	 */
	protected $widget = 'datetime';
	/**
	 * {@inheritDoc}
	 */
	protected $data_type = 'date';

	/**
	 * {@inheritDoc}
	 */
	public function setValue($value) {
		if(is_array($value) && isset($value['year']) && isset($value['month']) && isset($value['day']) && isset($value['hour']) && isset($value['minute']) && isset($value['second']))
			$this->value = \Carbon\Carbon::create($value['year'], $value['month'], $value['day'], $value['hour'], $value['minute'], $value['second']);
		elseif(is_string($value))
			$this->value = \Carbon\Carbon::createFromFormat('Y-m-d', $value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function value() {
		if(isset($this->options['data_type'])) {
			if($this->options['data_type'] == 'date')
				return $this->value;
			elseif($this->options['data_type'] == 'string')
				return $this->value->format('Y-m-d H:i:s');
		}
		return $this->value;
	}

	/**
	 * Return the hours.
	 * @return integer
	 */
	public function getHour() {
		if(!$this->value)
			return null;
		return $this->value->format('H');
	}

	/**
	 * Return the minutes.
	 * @return integer
	 */
	public function getMinute() {
		if(!$this->value)
			return null;
		return $this->value->format('i');
	}

	/**
	 * Return the seconds.
	 * @return integer
	 */
	public function getSecond() {
		if(!$this->value)
			return null;
		return $this->value->format('s');
	}

	/**
	 * Return the day.
	 * @return integer
	 */
	public function getDay() {
		if(!$this->value)
			return null;
		return $this->value->format('d');
	}

	/**
	 * Return the month.
	 * @return integer
	 */
	public function getMonth() {
		if(!$this->value)
			return null;
		return $this->value->format('m');
	}

	/**
	 * Return the year.
	 * @return integer
	 */
	public function getYear() {
		if(!$this->value)
			return null;
		return $this->value->format('Y');
	}
}