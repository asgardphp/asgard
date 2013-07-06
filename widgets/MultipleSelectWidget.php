<?php
namespace Coxis\Form\Widgets;

class MultipleSelectWidget extends \Coxis\Form\Widgets\HTMLWidget {
	public function render($options=array()) {
		$options = $this->options+$options;

		$attrs = array();
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		$attrs['multiple'] = 'multiple';

		$value = $this->value;
		$choices = isset($options['choices']) ? $options['choices']:array();

		return HTMLHelper::tag('select', array(
			'name'	=>	$this->name.'[]',
			'id'	=>	isset($options['id']) ? $options['id']:null,
		)+$attrs, function() use($choices, $value) {
			$str = '';
			foreach($choices as $k=>$v)
				if(is_array($value) && in_array($k, $value))
					$str .= HTMLHelper::tag('option', array(
						'value'	=>	$k,
						'selected'	=>	'selected',
					), $v);
				else
					$str .= HTMLHelper::tag('option', array(
						'value'	=>	$k,
					), $v);
			return $str;
		});
	}
}