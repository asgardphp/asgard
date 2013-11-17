<?php
namespace Coxis\Value\Models;

class Value extends \Coxis\Value\Models\SingleValue {
	public static $properties = array(
		'key',
		'value'    => array(
			'type' => 'longtext',
			'required'    =>    false,
		),
	);
}