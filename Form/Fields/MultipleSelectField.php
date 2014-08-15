<?php
namespace Asgard\Form\Fields;

class MultipleSelectField extends \Asgard\Form\Field {
	protected $widget = 'checkboxes';

	public function getChoices() {
		if(isset($this->options['choices']))
			return $this->options['choices'];
		return [];
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

		if(in_array($value, $default))
			$options['attrs']['checked'] = 'checked';
		$options['label'] = $name;

		$class = $this->getParent()->getWidgetsManager()->getWidget('checkbox');
		return $this->getTopForm()->getWidget($class, $this->name.'[]', $value, $options);
	}
	
	public function getValue() {
		if(!$this->value)
			return [];
		return $this->value;
	}
}