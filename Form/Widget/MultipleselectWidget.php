<?php
namespace Asgard\Form\Widget;

/**
 * Multiple select widget.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class MultipleselectWidget extends \Asgard\Form\Widget {
	/**
	 * {@inheritDoc}
	 */
	public function render(array $options=[]) {
		$options = $this->options+$options;

		$attrs = [];
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		$attrs['multiple'] = 'multiple';

		$value = $this->value;
		$choices = isset($options['choices']) ? $options['choices']:[];

		$str = '';
		foreach($choices as $k=>$v) {
			if(is_array($v)) {
				$_str = '';
				foreach($v as $_k=>$_v) {
					if(is_array($value) && in_array($_k, $value)) {
						$_str .= \Asgard\Form\HTMLHelper::tag('option', [
							'value'	=>	$_k,
							'selected'	=>	'selected',
						], $_v);
					}
					else {
						$_str .= \Asgard\Form\HTMLHelper::tag('option', [
							'value'	=>	$_k,
						], $_v);
					}
				}
				$str .= \Asgard\Form\HTMLHelper::tag('optgroup', [
					'label'	=>	$k,
				], $_str);
			}
			else {
				if(is_array($value) && in_array($k, $value)) {
					$str .= \Asgard\Form\HTMLHelper::tag('option', [
						'value'	=>	$k,
						'selected'	=>	'selected',
					], $v);
				}
				else {
					$str .= \Asgard\Form\HTMLHelper::tag('option', [
						'value'	=>	$k,
					], $v);
				}
			}
		}

		return \Asgard\Form\HTMLHelper::tag('select', [
			'name'	=>	$this->name.'[]',
			'id'	=>	isset($options['id']) ? $options['id']:null,
		]+$attrs, $str);
	}
}