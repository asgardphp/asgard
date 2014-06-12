<?php
namespace Asgard\Form\Widgets;

class DateWidget extends \Asgard\Form\Widget {
	public function render(array $options=[]) {
		$options = $this->options+$options;

		$day = $this->field->getDay();
		$month = $this->field->getMonth();
		$year = $this->field->getYear();

		return $this->field->getTopForm()->getWidget('select', $this->field->getName().'[day]', $day, ['choices'=>array_combine(range(1, 31), range(1, 31))])->render().
			$this->field->getTopForm()->getWidget('select', $this->field->getName().'[month]', $month, ['choices'=>array_combine(range(1, 12), range(1, 12))])->render().
			$this->field->getTopForm()->getWidget('select', $this->field->getName().'[year]', $year, ['choices'=>array_combine(range(date('Y'), date('Y')-50), range(date('Y'), date('Y')-50))])->render();
	}
}