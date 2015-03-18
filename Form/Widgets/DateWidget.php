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

		$days = array_combine(range(1, 31), range(1, 31));
		$months = array_combine(range(1, 12), range(1, 12));
		$years = array_combine(range(date('Y'), date('Y')-50), range(date('Y'), date('Y')-50));

		return $form->getWidget('select', $this->field->name().'[day]', $day, ['id'=>$this->field->getID().'-day', 'choices'=>$days]).
			$form->getWidget('select', $this->field->name().'[month]', $month, ['id'=>$this->field->getID().'-month', 'choices'=>$months]).
			$form->getWidget('select', $this->field->name().'[year]', $year, ['id'=>$this->field->getID().'-year', 'choices'=>$years]);
	}
}