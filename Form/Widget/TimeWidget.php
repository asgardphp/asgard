<?php
namespace Asgard\Form\Widget;

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

		$seconds = array_combine(range(1, 60), range(1, 60));
		$minutes = array_combine(range(1, 60), range(1, 60));
		$hours = array_combine(range(1, 24), range(1, 24));

		return
			$form->getWidget('select', $this->field->name().'[second]', $second, ['id'=>$this->field->getID().'-second', 'choices'=>$seconds]).
			$form->getWidget('select', $this->field->name().'[minute]', $minute, ['id'=>$this->field->getID().'-minute', 'choices'=>$minutes]).
			$form->getWidget('select', $this->field->name().'[hour]', $hour, ['id'=>$this->field->getID().'-hour', 'choices'=>$hours]);
	}
}