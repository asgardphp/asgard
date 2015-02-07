<?php
namespace Asgard\Form\Widgets;

/**
 * Radios widget.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class RadiosWidget extends \Asgard\Form\Widget {
	/**
	 * {@inheritDoc}
	 */
	public function render(array $options=[]) {
		$options = $this->options+$options;
		$form = $this->field->getParent()->getTopForm();

		$str = '';
		foreach($this->field->getChoices() as $k=>$v) {
			$options = [];
			if($k == $this->field->value())
				$options['attrs']['checked'] = 'checked';
			$str .= $form->getWidget('radio', $this->field->name(), $k, $options).' '.ucfirst($v).' ';
		}
		return $str;
	}
}