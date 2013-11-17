<?php
namespace Coxis\Value\Models;

class FileValue extends \Coxis\Value\SingleValue {
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