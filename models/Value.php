<?php
namespace App\Value\Models;

class Value extends \App\Value\Models\SingleValue {
	public static $properties = array(
		'key',
		'value'    => array(
			'type' => 'longtext',
			'required'    =>    false,
		),
	);
}