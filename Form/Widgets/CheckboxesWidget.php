<?php
namespace Asgard\Form\Widgets;

class CheckboxesWidget extends \Asgard\Form\Widget {
	public function render(array $options=[]) {
		$options = $this->options+$options;

		$str = '';
		foreach($this->field->getChoices() as $k=>$v) {
			$options = [];
			if(is_array($this->field->getValue()) && in_array($k, $this->field->getValue()) || $k==$this->field->getValue())
				$options['attrs']['checked'] = 'checked';
			$str .= $this->field->getTopForm()->getWidget('checkbox', $this->field->getName().'[]', $k, $options)->render().' '.ucfirst($v).' ';
		}
		return $str;
	}
}