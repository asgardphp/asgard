<?php
namespace Asgard\Form\Fields;

class DayField extends Field {
	public function __construct(array $params=[]) {
		$params['validation']['type'] = 'integer';
		$params['choices'] = ['Day'];
		foreach(range(1, 31) as $i)
			$params['choices'][$i] = $i;
		parent::__construct($params);
	}
}