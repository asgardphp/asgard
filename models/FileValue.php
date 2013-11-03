<?php
namespace App\Value\Models;

class FileValue extends \App\Value\SingleValue {
	public static $properties = array(
		'key',
		'value'    => array(
			'type'	=>	'file',
			'filetype'	=>	'file',
			'dir'	=>	'files',
			'required'    =>    false,
		),
	);
}