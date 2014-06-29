<?php
namespace Asgard\Form\Widgets;

class CheckboxWidget extends \Asgard\Form\Widget {
	public function render(array $options=[]) {
		$options = $this->options+$options;

		$attrs = [];
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		if($this->field && $this->field->value())
			$attrs['checked'] = 'checked';
		return \Asgard\Form\HTMLHelper::tag('input', [
			'type'	=>	'checkbox',
			'name'	=>	$this->name,
			'value'	=>	1,
		]+$attrs);
	}
}