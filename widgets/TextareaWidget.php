<?php
namespace Asgard\Form\Widgets;

class TextareaWidget extends \Asgard\Form\Widgets\HTMLWidget {
	public function render($options=array()) {
		$options = $this->options+$options;
		
		$attrs = array();
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		return HTMLHelper::tag('textarea', array(
			'name'	=>	$this->name,
			'id'	=>	isset($options['id']) ? $options['id']:null,
		)+$attrs,
		$this->value ? HTML::sanitize($this->value):'');
	}
}