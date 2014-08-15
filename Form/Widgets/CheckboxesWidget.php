<?php
namespace Asgard\Form\Widgets;

class CheckboxesWidget extends \Asgard\Form\Widget {
	public function render(array $options=[]) {
		$options = $this->options+$options;

		$str = '';
		foreach($this->field->getChoices() as $k=>$v) {
			$options = [];
			if(is_array($this->field->value()) && in_array($k, $this->field->value()) || $k==$this->field->value())
				$options['attrs']['checked'] = 'checked';
			$class = $this->field->getParent()->getWidgetsManager()->getWidget('checkbox');
			$str .= $this->field->getParent()->getWidget($class, $this->field->name().'[]', $k, $options)->render().' '.ucfirst($v).' ';
		}
		return $str;
	}
}