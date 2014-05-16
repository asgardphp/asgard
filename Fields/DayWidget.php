<?php
namespace Asgard\Form\Fields;

class DayField extends Field {
	public function __construct(array $params=array()) {
		$params['validation']['type'] = 'integer';
		$params['choices'] = array('Day');
		foreach(range(1, 31) as $i)
			$params['choices'][$i] = $i;
		parent::__construct($params);
	}
}