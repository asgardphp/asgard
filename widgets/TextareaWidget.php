<?php
namespace Coxis\Form\Widgets;

class TextareaWidget extends \Coxis\Form\Widgets\HTMLWidget {
	public function render($options=null) {
		if($options === null)
			$options = $this->options;
		
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