<?php
namespace Asgard\Data\Entities;

class Data extends Base {
	public static $properties = array(
		'key',
		'value'    => array(
			'type' => 'longtext',
			'required'    =>    false,
		),
	);
}