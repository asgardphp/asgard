<?php
namespace Asgard\Form\Widgets;

class RadioWidget extends \Asgard\Form\Widget {
	public function render(array $options=[]) {
		$options = $this->options+$options;
		
		$attrs = [];
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		return \Asgard\Form\HTMLHelper::tag('input', [
			'type'	=>	'radio',
			'name'	=>	$this->name,
			'value'	=>	$this->value,
		]+$attrs);
	}
}