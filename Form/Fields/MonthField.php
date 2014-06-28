<?php
namespace Asgard\Form\Fields;

class MonthField extends \Asgard\Form\Field {
	public function __construct(array $options=[]) {
		$options['validation']['type'] = 'integer';
		$options['choices'] = ['Month'];
		foreach(range(1, 12) as $i)
			$options['choices'][$i] = $i;
		parent::__construct($options);
	}
}