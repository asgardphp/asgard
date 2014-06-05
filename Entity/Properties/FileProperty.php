<?php
namespace Asgard\Entity\Properties;

class FileProperty extends \Asgard\Entity\Property {
	protected static $defaultExtensions = array('pdf', 'doc', 'jpg', 'jpeg', 'png', 'docx', 'gif', 'rtf', 'ppt', 'xls', 'zip', 'txt');

	public function __construct($params) {
		$params['extensions'] = static::$defaultExtensions;
		parent::__construct($params);
	}

	public function getRules() {
		$rules = parent::getRules();
		$rules['isNull'] = function($input) {
			return !$input->src();
		};
		if(!isset($rules['extension']))
			$rules['extension'] = $this->get('extensions');

		return $rules;
	}

	public function getSQLType() {
		if($this->multiple)
			return 'text';
		else
			return 'varchar(255)';
	}

	protected function _getDefault($entity=null) {
		if($this->multiple)
			return array();
		else
			return null;
	}

	protected function doSerialize($obj) {
		if(is_object($obj))
			return $obj->src();
	}

	protected function doUnserialize($str, $entity=null) {
		if(!$str)
			return null;
		$file = new \Asgard\Files\File($str);
		$file->setWebDir($this->definition->getApp()['kernel']['webdir']);
		$file->setUrl($this->definition->getApp()['request']->url);
		return $file;
	}

	protected function doSet($val, $entity=null) {
		if(is_string($val) && $val !== null)
			$val = new \Asgard\Files\File($val);
		if(is_object($val)) {
			$val->setWebDir($this->definition->getApp()['kernel']['webdir']);
			$val->setUrl($this->definition->getApp()['request']->url);
		}
		return $val;
	}
}