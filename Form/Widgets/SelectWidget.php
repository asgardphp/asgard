<?php
namespace Asgard\Form\Widgets;

/**
 * Select widget.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class SelectWidget extends \Asgard\Form\Widget {
	/**
	 * {@inheritDoc}
	 */
	public function render(array $options=[]) {
		$options = $this->options+$options;

		$attrs = [];
		if(isset($options['attrs']))
			$attrs = $options['attrs'];

		$value = $this->value;
		$choices = isset($options['choices']) ? $options['choices']:[];

		if($this->field
			&& $this->field->getOption('placeholder')
			&& !isset($options['placeholder'])
			&& $this->field->required()) {
				$options['placeholder'] = true;
		}
		if(isset($options['placeholder']) && $options['placeholder']) {
			$placeholder = $options['placeholder'];
			if($placeholder === true) {
				if($translator = $this->form->getTopForm()->getTranslator())
					$placeholder = $translator->trans('Choose');
				else
					$placeholder = 'Choose';
			}
			$choices = [''=>$placeholder] + $choices;
		}

		$str = '';
		foreach($choices as $k=>$v) {
			if($value == $k) {
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

		return \Asgard\Form\HTMLHelper::tag('select', [
			'name'	=>	$this->name,
			'id'	=>	isset($options['id']) ? $options['id']:null,
		]+$attrs, $str);
	}
}