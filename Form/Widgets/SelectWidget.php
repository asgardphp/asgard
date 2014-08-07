<?php
namespace Asgard\Form\Widgets;

class SelectWidget extends \Asgard\Form\Widget {
	public function render(array $options=[]) {
		$options = $this->options+$options;

		$attrs = [];
		if(isset($options['attrs']))
			$attrs = $options['attrs'];

		$value = $this->value;
		$choices = isset($options['choices']) ? $options['choices']:[];

		$str = '';
		foreach($choices as $k=>$v) {
			if($value == $k)
				$str .= \Asgard\Form\HTMLHelper::tag('option', [
					'value'	=>	$k,
					'selected'	=>	'selected',
				], $v);
			else
				$str .= \Asgard\Form\HTMLHelper::tag('option', [
					'value'	=>	$k,
				], $v);
		}

		return \Asgard\Form\HTMLHelper::tag('select', [
			'name'	=>	$this->name,
			'id'	=>	isset($options['id']) ? $options['id']:null,
		]+$attrs, $str);
	}
}