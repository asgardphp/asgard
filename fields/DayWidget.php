<?php
namespace Coxis\Form\Fields;

class DayField extends Field {
	function __construct($params=array()) {
		$params['validation']['type'] = 'integer';
		$params['choices'] = array('Day');
		foreach(range(1, 31) as $i)
			$params['choices'][$i] = $i;
		parent::__construct($params);
	}
}