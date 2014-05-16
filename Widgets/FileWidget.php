<?php
namespace Asgard\Form\Widgets;

class FileWidget extends \Asgard\Form\Widgets\HTMLWidget {
	public function render(array $options=array()) {
		$options = $this->options+$options;
		
		$attrs = array();
		if(isset($options['attrs']))
			$attrs = $options['attrs'];

		$str = \Asgard\Form\HTMLHelper::tag('input', array(
			'type'	=>	'file',
			'name'	=>	$this->name,
			'id'	=>	isset($options['id']) ? $options['id']:null,
		)+$attrs);

		return $str;
	}
}