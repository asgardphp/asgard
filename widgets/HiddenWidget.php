<?php
namespace Asgard\Form\Widgets;

class HiddenWidget extends \Asgard\Form\Widgets\HTMLWidget {
	public function render($options=array()) {
		$options = $this->options+$options;
		
		$attrs = array();
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		return HTMLHelper::tag('input', array(
			'type'	=>	'hidden',
			'name'	=>	$this->name,
			'value'	=>	$this->value,
			'id'	=>	isset($options['id']) ? $options['id']:null,
		)+$attrs);
	}
}