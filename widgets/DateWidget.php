<?php
namespace Coxis\Form\Widgets;

class DateWidget extends \Coxis\Form\Widgets\HTMLWidget {
	public function render($options=array()) {
		$options = $this->options+$options;

		$day = $this->field->getDay();
		$month = $this->field->getMonth();
		$year = $this->field->getYear();

		return HTMLWidget::select($this->field->getName().'[day]', $day, array('choices'=>array_combine(range(1, 31), range(1, 31))))->render().
		       HTMLWidget::select($this->field->getName().'[month]', $month, array('choices'=>array_combine(range(1, 12), range(1, 12))))->render().
		       HTMLWidget::select($this->field->getName().'[year]', $year, array('choices'=>array_combine(range(date('Y'), date('Y')-50), range(date('Y'), date('Y')-50))))->render();
	}
}