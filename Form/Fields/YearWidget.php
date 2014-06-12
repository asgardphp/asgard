<?php
namespace Asgard\Form\Fields;

class YearField extends Field {
	public function __construct(array $params=[]) {
		$params['validation']['type'] = 'integer';
		$params['choices'] = ['Year'];
		foreach(array_reverse(range(date('Y')-100, date('Y'))) as $i)
			$params['choices'][$i] = $i;
		parent::__construct($params);
	}
}