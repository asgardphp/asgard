<?php
namespace Asgard\Form\Widgets;

class CheckboxWidget extends \Asgard\Form\Widget {
	public function render(array $options=array()) {
		$options = $this->options+$options;

		$attrs = array();
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		if($this->field && $this->field->getValue())
			$attrs['checked'] = 'checked';
		return \Asgard\Form\HTMLHelper::tag('input', array(
			'type'	=>	'checkbox',
			'name'	=>	$this->name,
			'value'	=>	1,
		)+$attrs);
	}
}