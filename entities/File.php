<?php
namespace Asgard\Data\Entities;

class File extends BaseData {
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