<?php
namespace Asgard\Form\Fields;

/**
 * Date field.
 */
class DateField extends \Asgard\Form\Field {
	/**
	 * {@inheritDoc}
	 */
	protected $widget = 'date';
	/**
	 * {@inheritDoc}
	 */
	protected $data_type = 'date';

	/**
	 * {@inheritDoc}
	 */
	public function setValue($value) {
		if(is_array($value) && isset($value['year']) && isset($value['month']) && isset($value['day']))
			$this->value = \Carbon\Carbon::createFromDate($value['year'], $value['month'], $value['day']);
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
				return $this->value->format('Y-m-d');
		}
		return $this->value;
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
	 * [gReturn the month.
	 * @return integer
	 */
	public function getMonth() {
		if(!$this->value)
			return null;
		return $this->value->format('m');
	}

	/**
	 * [Return the year.
	 * @return integer
	 */
	public function getYear() {
		if(!$this->value)
			return null;
		return $this->value->format('Y');
	}
}