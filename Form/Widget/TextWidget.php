<?php
namespace Asgard\Form\Widget;

/**
 * Text widget.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class TextWidget extends \Asgard\Form\Widget {
	/**
	 * {@inheritDoc}
	 */
	public function render(array $options=[]) {
		$options = $this->options+$options;

		$attrs = [];
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		return \Asgard\Form\HTMLHelper::tag('input', [
			'type'	=>	'text',
			'name'	=>	$this->name,
			'value'	=>	$this->value,
			'id'	=>	isset($options['id']) ? $options['id']:null,
		]+$attrs);
	}
}