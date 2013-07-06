<?php
namespace Coxis\Form\Widgets;

class TextWidget extends \Coxis\Form\Widgets\HTMLWidget {
	public function render($options=null) {
		if($options === null)
			$options = $this->options;
		
		$attrs = array();
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		return HTMLHelper::tag('input', array(
			'type'	=>	'text',
			'name'	=>	$this->name,
			'value'	=>	$this->value,
			'id'	=>	isset($options['id']) ? $options['id']:null,
		)+$attrs);
	}
}