<?php
namespace Asgard\Form\Widgets;

class TextWidget extends \Asgard\Form\Widget {
	public function render(array $options=array()) {
		$options = $this->options+$options;
		
		$attrs = array();
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		return \Asgard\Form\HTMLHelper::tag('input', array(
			'type'	=>	'text',
			'name'	=>	$this->name,
			'value'	=>	$this->value,
			'id'	=>	isset($options['id']) ? $options['id']:null,
		)+$attrs);
	}
}