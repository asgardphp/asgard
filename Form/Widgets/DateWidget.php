<?php
namespace Asgard\Form\Widgets;

/**
 * Dage widget.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class DateWidget extends \Asgard\Form\Widget {
	/**
	 * {@inheritDoc}
	 */
	public function render(array $options=[]) {
		$options = $this->options+$options;

		$day = $this->field->getDay();
		$month = $this->field->getMonth();
		$year = $this->field->getYear();

		$form = $this->field->getParent()->getTopForm();

		return $form->getWidget('select', $this->field->name().'[day]', $day, ['id'=>$this->field->getID().'-day', 'choices'=>array_combine(range(1, 31), range(1, 31))]).
			$form->getWidget('select', $this->field->name().'[month]', $month, ['id'=>$this->field->getID().'-month', 'choices'=>array_combine(range(1, 12), range(1, 12))]).
			$form->getWidget('select', $this->field->name().'[year]', $year, ['id'=>$this->field->getID().'-year', 'choices'=>array_combine(range(date('Y'), date('Y')-50), range(date('Y'), date('Y')-50))]);
	}
}