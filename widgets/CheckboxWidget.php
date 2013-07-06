<?php
namespace Coxis\Form\Widgets;

class CheckboxWidget extends \Coxis\Form\Widgets\HTMLWidget {
	public function render($options=null) {
		if($options === null)
			$options = $this->options;

		$attrs = array();
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		if($this->field && $this->field->getValue())
			$attrs['checked'] = 'checked';
		return HTMLHelper::tag('input', array(
			'type'	=>	'checkbox',
			'name'	=>	$this->name,
			'value'	=>	1,
		)+$attrs);
	}
}