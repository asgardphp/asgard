<?php
namespace Asgard\Form\Widgets;

class MultipleSelectWidget extends \Asgard\Form\Widget {
	public function render(array $options=array()) {
		$options = $this->options+$options;

		$attrs = array();
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		$attrs['multiple'] = 'multiple';

		$value = $this->value;
		$choices = isset($options['choices']) ? $options['choices']:array();

		return \Asgard\Form\HTMLHelper::tag('select', array(
			'name'	=>	$this->name.'[]',
			'id'	=>	isset($options['id']) ? $options['id']:null,
		)+$attrs, function() use($choices, $value) {
			$str = '';
			foreach($choices as $k=>$v)
				if(is_array($value) && in_array($k, $value))
					$str .= \Asgard\Form\HTMLHelper::tag('option', array(
						'value'	=>	$k,
						'selected'	=>	'selected',
					), $v);
				else
					$str .= \Asgard\Form\HTMLHelper::tag('option', array(
						'value'	=>	$k,
					), $v);
			return $str;
		});
	}
}