<?php
namespace Asgard\Form\Fields;

class YearField extends \Asgard\Form\Fields\SelectField {
	public function __construct(array $options=[]) {
		$options['validation']['type'] = 'integer';
		$options['choices'] = ['Year'];
		foreach(array_reverse(range(date('Y')-100, date('Y'))) as $i)
			$options['choices'][$i] = $i;
		parent::__construct($options);
	}
}