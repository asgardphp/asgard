<?php
namespace Coxis\Value\Entities;

class Value extends \Coxis\Value\Entities\SingleValue {
	public static $properties = array(
		'key',
		'value'    => array(
			'type' => 'longtext',
			'required'    =>    false,
		),
	);
}