<?php
namespace Asgard\Form\Fields;

/**
 * Date field.
 * @author Michel Hognerud <michel@hognerud.com>
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
			$this->value = \Asgard\Common\Date::createFromDate($value['year'], $value['month'], $value['day']);
		elseif(is_string($value)) {
			try {
				$this->value = \Asgard\Common\Date::createFromFormat('Y-m-d', $value);
			} catch(\Exception $e) {
			}
		}
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