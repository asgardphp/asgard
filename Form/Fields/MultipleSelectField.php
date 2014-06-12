<?php
namespace Asgard\Form\Fields;

class MultipleSelectField extends \Asgard\Form\Field {
	protected $default_render = 'checkboxes';

	public function getChoices() {
		if(isset($this->options['choices']))
			return $this->options['choices'];
		return [];
	}

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
		return $this->getTopForm()->getWidget('radio', $this->getName(), $value, $options);
	}

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

	public function getCheckboxes(array $options=[]) {
		if(isset($options['choices']))
			$choices = $options['choices'];
		else
			$choices = $this->getChoices();

		$checkboxes = [];
		foreach($choices as $k=>$v) {
			$checkbox_options = $options;
			$checkbox_options['value'] = $k;
			$checkbox_options['widget_name'] = $v;
			$checkboxes[$k] = $this->getCheckbox($v, $checkbox_options);
		}
		return $checkboxes;
	}

	public function getCheckbox($name, array $options=[]) {
		$choices = $this->getChoices();
		$default = $this->value;

		$value = isset($options['value']) ? $options['value']:null;
		if($value===null) {
			foreach($choices as $k=>$v) {
				if($v == $name) {
					$value = $k;
					break;
				}
			}
		}
		if($value === null)
			throw new \Exception('No value for checkbox '.$name);

		if($value == $default)
			$options['attrs']['checked'] = 'checked';
		$options['label'] = $name;
		return $this->getTopForm()->getWidget('checkbox', $this->name, $value, $options);
	}
	
	public function getValue() {
		if(!$this->value)
			return [];
		return $this->value;
	}
}