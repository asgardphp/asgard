<?php
namespace Asgard\Form\Fields;

/**
 * Select field.
 */
class SelectField extends \Asgard\Form\Field {
	/**
	 * {@inheritDoc}
	 */
	protected $widget = 'select';

	/**
	 * Return choices.
	 * @return array
	 */
	public function getChoices() {
		if(isset($this->options['choices']))
			return $this->options['choices'];
		return [];
	}

	/**
	 * Return a radio widget.
	 * @param  string $name
	 * @param  array $options
	 * @return Widget
	 */
	public function getRadio($name, array $options=[]) {
		$choices = $this->getChoices();
		$default = $this->value;

		$value = isset($options['value']) ? $options['value']:null;
		if($value === null) {
			foreach($choices as $k=>$v) {
				if($v == $name) {
					$value = $k;
					break;
				}
			}
		}
		if($value === null)
			throw new \Exception('No value for radio '.$name);

		if($value == $default)
			$options['attrs']['checked'] = 'checked';
		$options['label'] = $name;

		return $this->getTopForm()->getWidget('radio', $this->name(), $value, $options);
	}

	/**
	 * Return radios widgets.
	 * @param  array $options
	 * @return array
	 */
	public function getRadios(array $options=[]) {
		if(isset($options['choices']))
			$choices = $options['choices'];
		else
			$choices = $this->getChoices();

		$radios = [];
		foreach($choices as $k=>$v) {
			$radio_options = $options;
			$radio_options['value'] = $k;
			$radio_options['widget_name'] = $v;
			$radios[$k] = $this->getRadio($v, $radio_options);
		}
		return $radios;
	}
}