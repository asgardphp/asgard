<?php
namespace Coxis\Form\Widgets;

class RadioWidget extends \Coxis\Form\Widgets\HTMLWidget {
	public function render($options=null) {
		if($options === null)
			$options = $this->options;
		
		$attrs = array();
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		return HTMLHelper::tag('input', array(
			'type'	=>	'radio',
			'name'	=>	$this->name,
			'value'	=>	$this->value,
		)+$attrs);
	}
}