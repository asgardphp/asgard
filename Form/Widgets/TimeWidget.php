<?php
namespace Asgard\Form\Widgets;

/**
 * Time widget.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class TimeWidget extends \Asgard\Form\Widget {
	/**
	 * {@inheritDoc}
	 */
	public function render(array $options=[]) {
		$options = $this->options+$options;

		$second = $this->field->getSecond();
		$minute = $this->field->getMinute();
		$hour = $this->field->getHour();

		$form = $this->field->getParent()->getTopForm();

		return
			$form->getWidget('select', $this->field->name().'[second]', $second, ['id'=>$this->field->getID().'-second', 'choices'=>array_combine(range(1, 60), range(1, 60))]).
			$form->getWidget('select', $this->field->name().'[minute]', $minute, ['id'=>$this->field->getID().'-minute', 'choices'=>array_combine(range(1, 60), range(1, 60))]).
			$form->getWidget('select', $this->field->name().'[hour]', $hour, ['id'=>$this->field->getID().'-hour', 'choices'=>array_combine(range(1, 24), range(1, 24))]);
	}
}