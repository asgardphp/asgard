<?php
namespace Coxis\Form\Widgets;

class RadiosWidget extends \Coxis\Form\Widgets\HTMLWidget {
	public function render($options=array()) {
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