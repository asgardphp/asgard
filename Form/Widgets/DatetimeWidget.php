<?php
namespace Asgard\Form\Widgets;

/**
 * Datetime widget.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class DatetimeWidget extends \Asgard\Form\Widget {
	/**
	 * {@inheritDoc}
	 */
	public function render(array $options=[]) {
		$options = $this->options+$options;

		$second = $this->field->getSecond();
		$minute = $this->field->getMinute();
		$hour = $this->field->getHour();
		$day = $this->field->getDay();
		$month = $this->field->getMonth();
		$year = $this->field->getYear();

		$form = $this->field->getParent()->getTopForm();

		$days = array_combine(range(1, 31), range(1, 31));
		$months = array_combine(range(1, 12), range(1, 12));
		$years = array_combine(range(date('Y'), date('Y')-50), range(date('Y'), date('Y')-50));
		$seconds = array_combine(range(1, 60), range(1, 60));
		$minutes = array_combine(range(1, 60), range(1, 60));
		$hours = array_combine(range(1, 24), range(1, 24));

		return
			$form->getWidget('select', $this->field->name().'[second]', $second, ['id'=>$this->field->getID().'-second', 'choices'=>$seconds]).
			$form->getWidget('select', $this->field->name().'[minute]', $minute, ['id'=>$this->field->getID().'-minute', 'choices'=>$minutes]).
			$form->getWidget('select', $this->field->name().'[hour]', $hour, ['id'=>$this->field->getID().'-hour', 'choices'=>$hours]).
			$form->getWidget('select', $this->field->name().'[day]', $day, ['id'=>$this->field->getID().'-day', 'choices'=>$days]).
			$form->getWidget('select', $this->field->name().'[month]', $month, ['id'=>$this->field->getID().'-month', 'choices'=>$months]).
			$form->getWidget('select', $this->field->name().'[year]', $year, ['id'=>$this->field->getID().'-year', 'choices'=>$years]);
	}
}