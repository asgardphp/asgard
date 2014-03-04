<?php
namespace Asgard\Form\Widgets;

class CheckboxesWidget extends \Asgard\Form\Widgets\HTMLWidget {
	public function render($options=array()) {
		$options = $this->options+$options;

		$str = '';
		foreach($this->field->getChoices() as $k=>$v) {
			$options = array();
			if(is_array($this->field->getValue()) && in_array($k, $this->field->getValue()) || $k==$this->field->getValue())
				$options['attrs']['checked'] = 'checked';
			$str .= HTMLWidget::checkbox($this->field->getName().'[]', $k, $options)->render().' '.ucfirst($v).' ';
		}
		return $str;
	}
}