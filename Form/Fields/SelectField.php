<?php
namespace Asgard\Form\Fields;

class SelectField extends \Asgard\Form\Field {
	protected $widget = 'select';

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

		$class = $this->getParent()->getWidgetsManager()->getWidget('radio');
		return $this->getParent()->getWidget($class, $this->name(), $value, $options);
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
}