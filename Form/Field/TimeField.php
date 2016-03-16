<?php
namespace Asgard\Form\Field;

/**
 * Time field.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class TimeField extends \Asgard\Form\Field {
	/**
	 * {@inheritDoc}
	 */
	protected $widget = 'time';
	/**
	 * {@inheritDoc}
	 */
	protected $data_type = 'date';

	/**
	 * {@inheritDoc}
	 */
	public function setValue($value) {
		if(is_array($value) && isset($value['hour']) && isset($value['minute']) && isset($value['second']))
			$this->value = \Asgard\Common\Time::createFromTime($value['hour'], $value['minute'], $value['second']);
		elseif(is_string($value))
			$this->value = \Asgard\Common\Time::createFromFormat('Y-m-d', $value);
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
}