<?php
namespace Asgard\Form\Widgets;

class RadiosWidget extends \Asgard\Form\Widgets\HTMLWidget {
	public function render(array $options=array()) {
		$options = $this->options+$options;

		$str = '';
		foreach($this->field->getChoices() as $k=>$v) {
			$options = array();
			if($k == $this->field->getValue())
				$options['attrs']['checked'] = 'checked';
			$str .= HTMLWidget::radio($this->field->getName(), $k)->render($options).ucfirst($v).' ';
		}
		return $str;
	}
}