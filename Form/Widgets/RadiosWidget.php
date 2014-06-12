<?php
namespace Asgard\Form\Widgets;

class RadiosWidget extends \Asgard\Form\Widget {
	public function render(array $options=[]) {
		$options = $this->options+$options;

		$str = '';
		foreach($this->field->getChoices() as $k=>$v) {
			$options = [];
			if($k == $this->field->getValue())
				$options['attrs']['checked'] = 'checked';
			$str .= $this->field->getTopForm()->getWidget('radio', $this->field->getName(), $k, $options)->render().' '.ucfirst($v).' ';
		}
		return $str;
	}
}