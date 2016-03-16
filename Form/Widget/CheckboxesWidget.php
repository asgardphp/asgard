<?php
namespace Asgard\Form\Widget;

/**
 * Checkboxes widget.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CheckboxesWidget extends \Asgard\Form\Widget {
	/**
	 * {@inheritDoc}
	 */
	public function render(array $options=[]) {
		$options = $this->options+$options;

		$str = '';
		foreach($this->field->getChoices() as $k=>$v) {
			$options = [];
			if(is_array($this->field->value()) && in_array($k, $this->field->value()) || $k==$this->field->value())
				$options['attrs']['checked'] = 'checked';

			$str .= $this->field->getParent()->getTopForm()->getWidget('checkbox', $this->field->name().'[]', $k, $options).' '.ucfirst($v).' ';
		}
		return $str;
	}
}