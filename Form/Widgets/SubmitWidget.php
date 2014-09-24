<?php
namespace Asgard\Form\Widgets;

/**
 * Submit widget.
 */
class SubmitWidget extends \Asgard\Form\Widget {
	/**
	 * {@inheritDoc}
	 */
	public function render(array $options=[]) {
		$options = $this->options+$options;
		
		$attrs = [];
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		return \Asgard\Form\HTMLHelper::tag('input', [
			'type'	=>	'submit',
			'name'	=>	$this->name,
			'value'	=>	$this->value,
			'id'	=>	isset($options['id']) ? $options['id']:null,
		]+$attrs);
	}
}