<?php
namespace Coxis\Value\Entities;

class FileValue extends \Coxis\Value\Entities\SingleValue {
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