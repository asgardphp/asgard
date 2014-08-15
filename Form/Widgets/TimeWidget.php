<?php
namespace Asgard\Form\Widgets;

class TimeWidget extends \Asgard\Form\Widget {
	public function render(array $options=[]) {
		$options = $this->options+$options;

		$second = $this->field->getSecond();
		$minute = $this->field->getMinute();
		$hour = $this->field->getHour();

		$class = $this->field->getParent()->getWidgetsManager()->getWidget('select');

		return
			$this->field->getParent()->getWidget($class, $this->field->name().'[second]', $second, ['id'=>$this->field->getID().'-second', 'choices'=>array_combine(range(1, 60), range(1, 60))])->render().
			$this->field->getParent()->getWidget($class, $this->field->name().'[minute]', $minute, ['id'=>$this->field->getID().'-minute', 'choices'=>array_combine(range(1, 60), range(1, 60))])->render().
			$this->field->getParent()->getWidget($class, $this->field->name().'[hour]', $hour, ['id'=>$this->field->getID().'-hour', 'choices'=>array_combine(range(1, 24), range(1, 24))])->render();
	}
}