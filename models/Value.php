<?php
namespace Coxis\App\Value\Models;

class Value extends \Coxis\App\Value\SingleValue {
	public static $properties = array(
		'key',
		'value'    => array(
			'required'    =>    false,
		),
	);
}