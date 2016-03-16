<?php
namespace Asgard\Form\Widget;

/**
 * Checkbox widget.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CheckboxWidget extends \Asgard\Form\Widget {
	/**
	 * {@inheritDoc}
	 */
	public function render(array $options=[]) {
		$options = $this->options+$options;

		$attrs = [];
		if(isset($options['attrs']))
			$attrs = $options['attrs'];
		if($this->field && $this->field->value())
			$attrs['checked'] = 'checked';
		if($this->value === true || $this->value === false)
			$this->value = '1';
		return \Asgard\Form\HTMLHelper::tag('input', [
			'type'	=>	'checkbox',
			'name'	=>	$this->name,
			'value'	=>	$this->value!==null ? $this->value:'1',
		]+$attrs);
	}
}