<?php
namespace Asgard\Form\Fields;

class MonthField extends Field {
	public function __construct(array $params=array()) {
		$params['validation']['type'] = 'integer';
		$params['choices'] = array('Month');
		foreach(range(1, 12) as $i)
			$params['choices'][$i] = $i;
		parent::__construct($params);
	}
}